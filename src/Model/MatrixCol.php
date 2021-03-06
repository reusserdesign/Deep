<?php

/**
 * Deep
 *
 * @package      rsanchez\Deep
 * @author       Rob Sanchez <info@robsanchez.com>
 */

namespace rsanchez\Deep\Model;

use Illuminate\Database\Eloquent\Builder;
use rsanchez\Deep\Collection\MatrixColCollection;

/**
 * Model for the matrix_cols table
 */
class MatrixCol extends AbstractProperty
{
    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $table = 'matrix_cols';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $primaryKey = 'col_id';

    /**
     * Filter by Field ID
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int|array                             $fieldId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFieldId(Builder $query, $fieldId)
    {
        $fieldId = is_array($fieldId) ? $fieldId : [$fieldId];

        return $this->whereIn('field_id', $fieldId);
    }

    /**
     * {@inheritdoc}
     *
     * @param  array                                         $models
     * @return \rsanchez\Deep\Collection\MatrixColCollection
     */
    public function newCollection(array $models = [])
    {
        return new MatrixColCollection($models);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->col_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'col_id_'.$this->col_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->col_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->col_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return 'col';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->col_label;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxLength()
    {
        $settings = $this->getSettings();

        return isset($settings['maxl']) ? $settings['maxl'] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings()
    {
        return @unserialize(base64_decode($this->col_settings)) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return $this->col_required === 'y';
    }

    /**
     * {@inheritdoc}
     */
    public function setRequired($required = true)
    {
        return $this->col_required = $required ? 'y' : 'n';
    }
}
