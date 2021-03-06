<?php

namespace Citadelle\PixMyCar\app;


class Vehicule extends Element
{

    const TABLE_ID = 3;


    static public function scopePublie(Table $query)
    {
        $query->where('Critheme10', '4 - Publié');
    }

    static public function scopeIdentifiants(Table $query, array $values)
    {
        if (count($values) == 1) {
            $query->where('Critheme3', $values[0]);
        } else {
            foreach ($values as $value) {
                $query->orWhere('Critheme3', $value);
            }
        }
    }

    protected function setIdThemeAttribute(string $value)
    {
        $this->attributes['id'] = $value;
    }

    protected function getPlayerAttribute()
    {
        return $this->attributes['Critheme6'];
    }

    protected function getIdentifiantAttribute()
    {
        return $this->attributes['Critheme3'];
    }


}
