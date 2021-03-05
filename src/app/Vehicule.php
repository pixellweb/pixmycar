<?php

namespace Citadelle\PixMyCar\app;


class Vehicule extends Element
{

    const TABLE_ID = 3;


    static public function scopePublie($query)
    {
        $query->where('Critheme10', '4 - Publié');
    }


    /**
     * @param string $value
     */
    protected function setCritheme3Attribute(string $value)
    {
        $this->attributes['immatriculation'] = $value;
    }

    protected function setIdThemeAttribute(string $value)
    {
        $this->attributes['id'] = $value;
    }

    protected function setCritheme6Attribute(string $value)
    {
        $this->attributes['player'] = $value;
    }



}
