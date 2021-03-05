<?php


namespace Citadelle\PixMyCar\app;


use App\Models\Source\Source;
use Citadelle\Icar\app\IcarException;
use Citadelle\PixMyCar\app\Api;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as CollectionLaravel;
use phpDocumentor\Reflection\Types\Integer;

class Table
{
    /**
     * @var Api
     */
    protected $api;



    /**
     * @var string
     */
    protected $class;

    protected $has_join = false;



    /**
     * @var int|null
     */
    protected $table;

    /**
     * @var string|null
     */
    protected $selection = 'SelTotale';

    /**
     * @var string|null
     */
    protected $rubsTri;

    /**
     * @var int|null
     */
    protected $numPage = 1;

    /**
     * @var int|null
     */
    protected $maxRecords = 1000;

    /**
     * @var string|null
     */
    protected $listeRubsAEnvoyer;

    /**
     * @var string|null
     */
    protected $rechercheSimple;

    /**
     * @var array|null
     */
    protected $recherche = [];


    const OPERATEURS_ALIAS = [
        '=' => 'O',
        '>' => 'Sup',
        '>=' => 'SupE',
        '<' => 'Inf',
        '<=' => 'InfE',
        '!=' => 'Diff',
        '<>' => 'Diff',

    ];


    /**
     * Fichier constructor.
     * @param $class
     */
    public function __construct(string $class)
    {
        $this->api = new Api();

        $this->class = $class;

        $this->table = $this->class::TABLE_ID;
    }



    public function get() : Collection
    {

        if (!empty($this->recherche) and $this->has_join) {
            throw new IcarException("Table::get  Erreur impossible de faire un where avec un join");
        }

        $elements = $this->api->select($this->table, $this->selection, $this->rubsTri, $this->numPage, $this->maxRecords, $this->listeRubsAEnvoyer, $this->rechercheSimple, $this->recherche);

        $collection = collect();
        foreach ($elements as $element) {
            $collection->push(new $this->class($this->api->getToken(), $element));
        }

        return $collection;

    }

    public function select(array $champs) : self
    {
        $this->listeRubsAEnvoyer = implode(';', $champs);
        return $this;
    }

    public function take($nombre)
    {
        if ($nombre > 1000) {
            throw new IcarException("Table::take  Erreur nombre d'élement doit être inférieur à 1000");
        }
        $this->maxRecords = $nombre;

        return $this;
    }

    public function orderBy(string $champ, string $order = 'asc') : self
    {
        $this->rubsTri = (strtolower($order) == 'desc' ? '-' : '').$champ;

        return $this;
    }

    public function page(int $numero) : self
    {
        $this->numPage = $numero;

        return $this;
    }

    public function where(string $champ, $comparateur, $texte = null, $operateur = 'ET') : self
    {
        if ($texte === null) {
            $texte = $comparateur;
            $comparateur = 'O';
        }

        if (isset(self::OPERATEURS_ALIAS[$comparateur])) {
            $comparateur = self::OPERATEURS_ALIAS[$comparateur];
        }

        $this->recherche[] = $champ.';'.$comparateur.';'.$texte.';'.$operateur;

        return $this;
    }

    public function orWhere(string $champ, $comparateur, $texte = null) : self
    {

        $this->where($champ, $comparateur, $texte, 'OU');

        return $this;
    }

    public function join(Element $element, int $primary_key) : self
    {

        $this->selection = 'LinkedRecords:'.$element::TABLE_ID.';'.$primary_key;

        $this->has_join = true;

        return $this;
    }


    public function __call($name, $arguments)
    {
        if (method_exists($this->class, 'scope'.ucfirst($name))) {
            $this->class::{'scope'.ucfirst($name)}($this, $arguments);
            return $this;
        }
    }


}

