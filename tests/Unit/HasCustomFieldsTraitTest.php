<?php

namespace Skylence\LaravelCustomFields\Tests\Unit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Skylence\LaravelCustomFields\Models\Field;
use Skylence\LaravelCustomFields\Tests\TestCase;
use Skylence\LaravelCustomFields\Traits\HasCustomFields;

class HasCustomFieldsTraitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_merges_custom_field_codes_into_fillable(): void
    {
        $testModel = new class extends Model
        {
            use HasCustomFields;

            protected $table = 'test_models';

            protected $fillable = ['name', 'email'];
        };

        Field::create([
            'name' => 'Custom Field',
            'code' => 'custom_field',
            'type' => 'text',
            'customizable_type' => get_class($testModel),
        ]);

        $testModel->loadCustomFields();

        $this->assertContains('custom_field', $testModel->getFillable());
        $this->assertContains('name', $testModel->getFillable());
        $this->assertContains('email', $testModel->getFillable());
    }

    /** @test */
    public function it_merges_custom_field_casts(): void
    {
        $testModel = new class extends Model
        {
            use HasCustomFields;

            protected $table = 'test_models';

            protected $fillable = [];
        };

        Field::create([
            'name' => 'Is Active',
            'code' => 'is_active',
            'type' => 'checkbox',
            'customizable_type' => get_class($testModel),
        ]);

        Field::create([
            'name' => 'Tags',
            'code' => 'tags',
            'type' => 'select',
            'is_multiselect' => true,
            'customizable_type' => get_class($testModel),
        ]);

        $testModel->loadCustomFields();

        $casts = $testModel->getCasts();

        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('array', $casts['tags']);
    }

    /** @test */
    public function it_can_get_custom_field_value(): void
    {
        $testModel = new class extends Model
        {
            use HasCustomFields;

            protected $table = 'test_models';

            protected $fillable = ['custom_rating'];

            public $custom_rating = 'Excellent';
        };

        $value = $testModel->getCustomField('custom_rating');

        $this->assertEquals('Excellent', $value);
    }

    /** @test */
    public function it_can_set_custom_field_value(): void
    {
        $testModel = new class extends Model
        {
            use HasCustomFields;

            protected $table = 'test_models';

            protected $fillable = ['custom_rating'];
        };

        $testModel->setCustomField('custom_rating', 'Good');

        $this->assertEquals('Good', $testModel->custom_rating);
    }
}
