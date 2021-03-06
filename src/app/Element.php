<?php


namespace Citadelle\PixMyCar\app;


use Carbon\Carbon;
use SimpleXMLElement;

class Element
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $dates = [];

    /**
     * @var array
     */
    protected $nullables = [];

    /**
     * @var string
     */
    protected $token;

    const DATE_FORMAT = 'Y-m-d\TH:i:s';


    /**
     * Icar constructor.
     * @param string $token
     * @param SimpleXMLElement $attributes
     */
    public function __construct(string $token, SimpleXMLElement $attributes)
    {
        $this->token = $token;
        $this->fill($attributes);
    }

    /**
     * @return Table
     */
    static public function query()
    {
        return new Table(static::class);
    }


    /**
     * @param SimpleXMLElement $attributes
     */
    public function fill(SimpleXMLElement $attributes)
    {
        // On affecte les $attributs en masse avant les setters
        // Cela permet de ne pas être tributaire de l'ordre des colonnes dans le csv
        foreach ($attributes as $property => $value) {
            $this->attributes[$property] = (string)$value;
        }
        foreach ($attributes as $property => $value) {
            $this->__set($property, (string)$value);
        }
    }


    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . lcfirst($name) . 'Attribute')) {
            $this->{'set' . lcfirst($name) . 'Attribute'}($value);
        } else {

            if (in_array($name, $this->dates)) {
                $value = $this->createDate($value);
            }

            if (in_array($name, $this->nullables)) {
                $value = $this->setNullable($value);
            }

            $this->attributes[$name] = $value;
        }

    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {

        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }

    /**
     * @param $value
     * @return Carbon|false|null
     */
    protected function createDate($value)
    {
        $date = null;
        try {
            $date = Carbon::createFromFormat(self::DATE_FORMAT, $value);
        } catch (\Exception $exception) {
            $date = null;
        }
        return $date;
    }


    /**
     * @param $value
     * @return mixed|null
     */
    protected function setNullable($value)
    {
        return empty($value) ? null : $value;
    }


}

