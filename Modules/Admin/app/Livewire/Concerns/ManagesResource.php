<?php

namespace Modules\Admin\Livewire\Concerns;

use Flux\Flux;
use Illuminate\Database\Eloquent\Model;

/**
 * Logika bersama untuk seluruh form pengelolaan (CRUD) di panel admin.
 *
 * Menyediakan pola modal yang seragam: buka form, simpan, dan hapus.
 * Setiap komponen cukup mengisi aturan validasi, atribut, dan model tujuan.
 */
trait ManagesResource
{
    /**
     * Apakah modal form sedang terbuka.
     */
    public bool $showModal = false;

    /**
     * Id record yang sedang disunting; null berarti membuat record baru.
     */
    public ?int $editingId = null;

    /**
     * Apakah modal konfirmasi hapus sedang terbuka.
     */
    public bool $confirmingDelete = false;

    /**
     * Id record yang sedang dikonfirmasi untuk dihapus.
     */
    public ?int $confirmingDeleteId = null;

    /**
     * Buka modal untuk membuat record baru.
     */
    public function create(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->editingId = null;
        $this->fill($this->defaultFormData());
        $this->showModal = true;
    }

    /**
     * Buka modal untuk menyunting record yang ada.
     */
    public function edit(int $id): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $record = $this->findRecord($id);

        $this->editingId = $record->getKey();
        $this->fill($this->formDataFrom($record));
        $this->showModal = true;
    }

    /**
     * Tutup modal dan bersihkan state form.
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    /**
     * Validasi lalu simpan record (baru atau hasil suntingan).
     */
    public function save(): void
    {
        $validated = $this->validate($this->rules(), attributes: $this->validationAttributes());

        if ($this->editingId === null) {
            $record = $this->newRecord();
            $record->fill($validated);
            $this->beforeSave($record, $validated);
            $record->save();

            Flux::toast(variant: 'success', text: $this->createdMessage());
        } else {
            $record = $this->findRecord($this->editingId);
            $record->fill($validated);
            $this->beforeSave($record, $validated);
            $record->save();

            Flux::toast(variant: 'success', text: $this->updatedMessage());
        }

        $this->closeModal();
    }

    /**
     * Minta konfirmasi sebelum menghapus.
     */
    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
        $this->confirmingDelete = true;
    }

    /**
     * Batalkan permintaan hapus.
     */
    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->confirmingDeleteId = null;
    }

    /**
     * Hapus record yang sudah dikonfirmasi.
     */
    public function delete(): void
    {
        if ($this->confirmingDeleteId === null) {
            $this->confirmingDelete = false;

            return;
        }

        $record = $this->findRecord($this->confirmingDeleteId);

        // Kait ini boleh menolak penghapusan, mis. bila record masih dipakai
        // oleh data lain. Kembalikan false untuk membatalkan.
        if ($this->beforeDelete($record) === false) {
            $this->cancelDelete();

            return;
        }

        $record->delete();

        $this->cancelDelete();

        Flux::toast(variant: 'success', text: $this->deletedMessage());
    }

    /**
     * Nilai awal form saat membuat record baru.
     *
     * @return array<string, mixed>
     */
    abstract protected function defaultFormData(): array;

    /**
     * Aturan validasi form.
     *
     * @return array<string, mixed>
     */
    abstract protected function rules(): array;

    /**
     * Ambil record berdasarkan id.
     */
    abstract protected function findRecord(?int $id): Model;

    /**
     * Buat instance model baru yang belum disimpan.
     */
    abstract protected function newRecord(): Model;

    /**
     * Isi form dari record yang akan disunting.
     *
     * @return array<string, mixed>
     */
    abstract protected function formDataFrom(Model $record): array;

    /**
     * Nama atribut dalam bahasa Indonesia untuk pesan validasi.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [];
    }

    /**
     * Kait sebelum record disimpan; dipakai untuk menangani unggahan berkas.
     *
     * @param  array<string, mixed>  $validated
     */
    protected function beforeSave(Model $record, array $validated): void {}

    /**
     * Kait sebelum record dihapus; dipakai untuk membersihkan berkas terkait
     * atau menolak penghapusan dengan mengembalikan false.
     */
    protected function beforeDelete(Model $record): ?bool
    {
        return true;
    }

    protected function createdMessage(): string
    {
        return 'Data berhasil ditambahkan.';
    }

    protected function updatedMessage(): string
    {
        return 'Data berhasil diperbarui.';
    }

    protected function deletedMessage(): string
    {
        return 'Data berhasil dihapus.';
    }
}
