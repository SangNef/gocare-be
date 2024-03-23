<?php

namespace App\Traits;

/**
 * @mixin \Eloquent
 */
trait CanSaveQuietly
{
    /**
     * Save the model without firing any model events
     *
     * @param array $options
     *
     * @return mixed
     */
    public function saveQuietly(array $options = [])
    {
        $dispatcher = static::getEventDispatcher();
        static::unsetEventDispatcher();

        $this->save($options);

        static::setEventDispatcher($dispatcher);
        return $this;
    }

    /**
     * Update the model without firing any model events
     *
     * @param array $attributes
     * @param array $options
     *
     * @return mixed
     */
    public function updateQuietly(array $attributes = [], array $options = [])
    {

        $dispatcher = static::getEventDispatcher();
        static::unsetEventDispatcher();

        $this->update($attributes, $options);

        static::setEventDispatcher($dispatcher);
        return $this;
    }
}
