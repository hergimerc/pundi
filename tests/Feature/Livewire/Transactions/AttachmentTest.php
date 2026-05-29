<?php

namespace Tests\Feature\Livewire\Transactions;

use App\Livewire\Transactions\Create;
use App\Livewire\Transactions\Edit;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_create_stores_uploaded_attachment(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();
        $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '50000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->set('attachments', [$file])
            ->call('save');

        $transaction = Transaction::first();
        $this->assertSame(1, $transaction->attachments()->count());

        $attachment = $transaction->attachments()->first();
        $this->assertSame('receipt.pdf', $attachment->original_name);
        $this->assertSame('application/pdf', $attachment->mime_type);
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_create_without_attachments_saves_none(): void
    {
        $account = Account::factory()->create();
        $category = Category::factory()->expense()->create();

        Livewire::actingAs($this->user)
            ->test(Create::class)
            ->set('type', 'expense')
            ->set('amount', '50000')
            ->set('account_id', (string) $account->id)
            ->set('category_id', (string) $category->id)
            ->set('transacted_at', now()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertSame(0, Attachment::count());
    }

    public function test_edit_stores_new_attachment(): void
    {
        $transaction = Transaction::factory()->expense()->create();
        $file = UploadedFile::fake()->image('photo.jpg');

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->set('newAttachments', [$file])
            ->call('save');

        $this->assertSame(1, $transaction->attachments()->count());
        $attachment = $transaction->attachments()->first();
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_edit_remove_attachment_deletes_file_and_record(): void
    {
        Storage::fake('public');
        $transaction = Transaction::factory()->expense()->create();
        $file = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
        $path = $file->store("attachments/{$transaction->id}", 'public');

        $attachment = $transaction->attachments()->create([
            'file_path' => $path,
            'original_name' => 'doc.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 51200,
        ]);

        Livewire::actingAs($this->user)
            ->test(Edit::class, ['transaction' => $transaction])
            ->call('removeAttachment', $attachment->id);

        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_show_displays_attachment_list(): void
    {
        $transaction = Transaction::factory()->expense()->create();
        $transaction->attachments()->create([
            'file_path' => 'attachments/1/file.pdf',
            'original_name' => 'invoice.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10240,
        ]);

        $this->actingAs($this->user)
            ->get(route('transactions.show', $transaction))
            ->assertSee('invoice.pdf');
    }

    public function test_deleting_transaction_cascades_attachment_records(): void
    {
        $transaction = Transaction::factory()->expense()->create();
        $transaction->attachments()->create([
            'file_path' => 'attachments/1/file.pdf',
            'original_name' => 'receipt.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 5120,
        ]);

        $transaction->forceDelete();

        $this->assertSame(0, Attachment::count());
    }
}
