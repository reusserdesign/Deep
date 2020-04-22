<?php

namespace rsanchez\Deep\Eloquent;

use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use rsanchez\Deep\Collection\FieldCollection;

class Builder extends BaseBuilder
{
    /**
     * Whether or not to hydrate custom fields
     * @var bool
     */
    protected $hydrationEnabled = true;

    /**
     * Whether or not to hydrate children's custom fields
     * @var bool
     */
    protected $childHydrationEnabled = true;

    /**
     * Which fields to hydrate
     * @var bool
     */
    protected $fieldsByChannelId = false;

    /**
     * Which fields to hydrate
     * @var bool
     */
    protected $withFields = [];

    protected $scopedChannelIds = [];
    private $hasAppliedFields = false;

    public function __construct(QueryBuilder $query, BaseBuilder $builder)
    {
        parent::__construct($query);
        $this->model = $builder->model;
        $this->eagerLoad = $builder->eagerLoad;
        $macrosProp = isset($builder->localMacros) ? 'localMacros' : 'macros';
        $this->{$macrosProp} = $builder->{$macrosProp};
        $this->onDelete = $builder->onDelete;
        $this->passthru = $builder->passthru;

        if (isset($builder->scopes)) {
            $this->scopes = $builder->scopes;
        }

        if (isset($builder->removedScopes)) {
            $this->removedScopes = $builder->removedScopes;
        }
    }

    /**
     * Get a Collection of fields from the specified group
     *
     * @param  int                                       $channelId
     * @return \rsanchez\Deep\Collection\FieldCollection
     */
    private function applyQueryFields() {

        if ($this->hasAppliedFields) return;
        $this->hasAppliedFields = true;

        $fieldCollection = collect([]);
        if (!empty($this->scopedChannelIds)) {
            foreach($this->scopedChannelIds as $channelId) {
                if (isset($this->fieldsByChannelId[$channelId])) {
                    $fieldCollection = $fieldCollection->concat($this->fieldsByChannelId[$channelId])->unique();
                }
            }
        }

        // join all the channel_data_field_X tables
        foreach ($fieldCollection as $field) {
            if ($field->legacy_field_data !== 'y') {
                $table = "channel_data_field_{$field->field_id}";
                $this->leftJoin($table, 'channel_titles.entry_id', '=', "{$table}.entry_id");
            }
        }
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = ['*'])
    {
        $builder = method_exists($this, 'applyScopes') ? $this->applyScopes() : $this;

        $builder->applyQueryFields();

        $models = $builder->getModels($columns);

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models, $builder);
    }

    public function setWithFields($fieldNames)
    {
        $this->withFields = $fieldNames;

        return $this;
    }

    public function setHydrationEnabled()
    {
        $this->hydrationEnabled = true;

        return $this;
    }

    public function setHydrationDisabled()
    {
        $this->hydrationEnabled = false;

        return $this;
    }

    public function setChildHydrationEnabled()
    {
        $this->childHydrationEnabled = true;

        return $this;
    }

    public function setChildHydrationDisabled()
    {
        $this->childHydrationEnabled = false;

        return $this;
    }

    public function getWithFields()
    {
        return $this->withFields;
    }

    public function isHydrationEnabled()
    {
        return $this->hydrationEnabled;
    }

    public function isChildHydrationEnabled()
    {
        return $this->childHydrationEnabled;
    }

    public function setFieldsByChannelId($fieldsByChannelId) {
        $this->fieldsByChannelId = $fieldsByChannelId;
    }

    public function setScopedChannelIds($scopedChannelIds) {
        $this->scopedChannelIds = $scopedChannelIds;
    }
}
