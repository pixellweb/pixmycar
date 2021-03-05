<?php


namespace Citadelle\PixMyCar\app;


use SoapClient;
use SoapFault;
use SimpleXMLElement;

/**
 * Class Api
 * @package App\Citadelle
 */
class Api
{

    /**
     * @var string
     */
    protected $token;

    protected $client;

    protected $langue = 1;



    /**
     * Api constructor.
     * @throws PixMyCarException
     */
    public function __construct()
    {
        try {

            $this->client = new SoapClient(config('pixmycar.api.url'));

        } catch (SoapFault $exception) {
            throw new PixMyCarException("Api::__construct : Problème d'accés à l'api", 0, $exception);
        }

        $this->token = $this->connect(config('pixmycar.api.pseudo'), config('pixmycar.api.password'));
    }


    /**
     * @param string $pseudo
     * @param string $password
     * @return string token
     * @throws PixMyCarException
     */
    public function connect(string $pseudo, string $password) :string
    {
        try {

            $response = $this->client->__soapCall("WS_Connect", [
                'Pseudo' => $pseudo,
                'Password' => $password,
                'NumLangue' => '1'
            ]);

        } catch (SoapFault $exception) {
            throw new PixMyCarException("Api::__construct : Problème de connexion à l'api", 0, $exception);
        }

        if (!empty($response['Jeton'])) {
            return $response['Jeton'];
        }

        throw new PixMyCarException('Api::connect : Erreur de connexion => '.$response['LibelleErreur']);
    }


    public function ping()
    {

        /*$datas = [
            'Jeton' => $this->token,
            '',
            '',
            1, // langue (1 pour français)
            3, // numtable (3 pour table véhicule)
            "SelTotale", // selection
            '', // droit
            '', // storeSel
            '-DateModifTh', // rubsTri
            0, // numpage
            10, // maxrecords
            "*", // liste rubs à renvoyer    ;document.IdDocument
            '', //recherche simple
            '', //recherche
            '',
            '',
            '',
            ''
        ];

        return $this->client->__soapCall('WS_Select', $datas);*/

        return $this->client->WS_Ping('Ping');
    }


    /**
     * @param int $numTtable
     * @param string $selection
     * @param string|null $droit
     * @param string|null $storeSel
     * @param string|null $rubsTri
     * @param int|null $numPage
     * @param int|null $maxRecords
     * @param string|null $listeRubsAEnvoyer
     * @param string|null $rechercheSimple
     * @param array $recherche
     * @return array
     * @throws PixMyCarException
     */
    public function select(int $numTtable, string $selection, string $rubsTri = null, int $numPage = null, int $maxRecords = null, string $listeRubsAEnvoyer = null, string $rechercheSimple = null, array $recherche = [], string $droit = null, string $storeSel = null)
    {

        try {

            $response = $this->client->WS_Select($this->token, '', '', $this->langue, $numTtable, $selection, $droit, $storeSel, $rubsTri, $numPage, $maxRecords, $listeRubsAEnvoyer, $rechercheSimple, $recherche, null, null, null, null);

        } catch (SoapFault $exception) {
            throw new PixMyCarException("Api::select : Problème de sélection ", 0, $exception);
        }

        $themes = new SimpleXMLElement($response['Records']);

        return $themes;
    }


    public function getToken()
    {
        return $this->token;
    }

}
