<?php

namespace Skylence\LaravelCustomFields\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Skylence\LaravelCustomFields\Http\Livewire\FieldCreate;
use Skylence\LaravelCustomFields\Http\Livewire\FieldEdit;
use Skylence\LaravelCustomFields\Http\Livewire\FieldIndex;
use Skylence\LaravelCustomFields\Models\Field;
use Skylence\LaravelCustomFields\Tests\TestCase;

class FieldManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_display_the_field_index_page(): void
    {
        Livewire::test(FieldIndex::class)
            ->assertStatus(200)
            ->assertSee('Custom Fields');
    }

    /** @test */
    public function it_can_display_the_field_create_page(): void
    {
        Livewire::test(FieldCreate::class)
            ->assertStatus(200)
            ->assertSee('Create Custom Field');
    }

    /** @test */
    public function it_can_create_a_custom_field(): void
    {
        Livewire::test(FieldCreate::class)
            ->set('name', 'Test Field')
            ->set('code', 'test_field')
            ->set('type', 'text')
            ->set('customizable_type', 'App\\Models\\User')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_fields', [
            'name' => 'Test Field',
            'code' => 'test_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_on_create(): void
    {
        Livewire::test(FieldCreate::class)
            ->set('name', '')
            ->set('code', '')
            ->set('customizable_type', '')
            ->call('save')
            ->assertHasErrors(['name', 'code', 'customizable_type']);
    }

    /** @test */
    public function it_validates_code_format(): void
    {
        Livewire::test(FieldCreate::class)
            ->set('name', 'Test Field')
            ->set('code', '123-invalid-code!')
            ->set('customizable_type', 'App\\Models\\User')
            ->call('save')
            ->assertHasErrors(['code']);
    }

    /** @test */
    public function it_can_display_the_field_edit_page(): void
    {
        $field = Field::create([
            'name' => 'Test Field',
            'code' => 'test_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Livewire::test(FieldEdit::class, ['field' => $field])
            ->assertStatus(200)
            ->assertSee('Edit Custom Field')
            ->assertSee('Test Field');
    }

    /** @test */
    public function it_can_update_a_custom_field(): void
    {
        $field = Field::create([
            'name' => 'Test Field',
            'code' => 'test_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Livewire::test(FieldEdit::class, ['field' => $field])
            ->set('name', 'Updated Field Name')
            ->set('type', 'textarea')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_fields', [
            'id' => $field->id,
            'name' => 'Updated Field Name',
            'type' => 'textarea',
        ]);
    }

    /** @test */
    public function it_can_soft_delete_a_field(): void
    {
        $field = Field::create([
            'name' => 'Test Field',
            'code' => 'test_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Livewire::test(FieldEdit::class, ['field' => $field])
            ->call('confirmDelete', false)
            ->call('delete');

        $this->assertSoftDeleted('custom_fields', [
            'id' => $field->id,
        ]);
    }

    /** @test */
    public function it_can_search_fields(): void
    {
        Field::create([
            'name' => 'First Field',
            'code' => 'first_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Field::create([
            'name' => 'Second Field',
            'code' => 'second_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Livewire::test(FieldIndex::class)
            ->set('search', 'First')
            ->assertSee('First Field')
            ->assertDontSee('Second Field');
    }

    /** @test */
    public function it_can_filter_fields_by_type(): void
    {
        Field::create([
            'name' => 'Text Field',
            'code' => 'text_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Field::create([
            'name' => 'Select Field',
            'code' => 'select_field',
            'type' => 'select',
            'customizable_type' => 'App\\Models\\User',
        ]);

        Livewire::test(FieldIndex::class)
            ->set('filterType', 'text')
            ->assertSee('Text Field')
            ->assertDontSee('Select Field');
    }

    /** @test */
    public function it_can_restore_a_soft_deleted_field(): void
    {
        $field = Field::create([
            'name' => 'Test Field',
            'code' => 'test_field',
            'type' => 'text',
            'customizable_type' => 'App\\Models\\User',
        ]);

        $field->delete();

        Livewire::test(FieldIndex::class)
            ->call('restoreField', $field->id);

        $this->assertDatabaseHas('custom_fields', [
            'id' => $field->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_can_add_options_to_select_field(): void
    {
        Livewire::test(FieldCreate::class)
            ->set('name', 'Priority Field')
            ->set('code', 'priority')
            ->set('type', 'select')
            ->set('customizable_type', 'App\\Models\\User')
            ->set('newOption', 'High')
            ->call('addOption')
            ->set('newOption', 'Medium')
            ->call('addOption')
            ->assertSet('options', ['High', 'Medium'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('custom_fields', [
            'code' => 'priority',
            'type' => 'select',
        ]);

        $field = Field::where('code', 'priority')->first();
        $this->assertEquals(['High', 'Medium'], $field->options);
    }
}
