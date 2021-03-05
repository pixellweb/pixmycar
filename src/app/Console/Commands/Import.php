<?php

namespace Citadelle\PixMyCar\app\Console\Commands;


use Citadelle\PixMyCar\app\Api;
use Citadelle\PixMyCar\app\Document;
use Citadelle\PixMyCar\app\Vehicule;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pixmycar {--O|option=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import des photos 360 ';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        ProgressBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% -- %message%');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $option = $this->option('option');


        switch ($option) {
            case 'test' :
                $this->test();
                break;
            case 'all' :
            default :
                $this->import();
                break;
        }

        $this->info(PHP_EOL . '**** Fin import ****');
    }

    private function test()
    {

        //$documents = Document::query()->select(['*'])->where('Critere3', "ET-296-QB")->orderBy('DateModifTh', 'desc')->take(1)->get();
        $documents = Document::query()->select(['*'])->where('TypeFichierPpal', 'Plate-forme Video')->orderBy('DateModifTh', 'desc')->take(1)->get();
        dd($documents);

        $vehicules = Vehicule::query()->select(['theme.IdTheme', 'theme.Critheme3', 'theme.TitreTheme', 'theme.Critheme6'])->take(2)->orderBy('DateModifTh', 'desc')->where('Critheme2', 'Land Cruiser')->get();

        dd($vehicules->first());

        $pixmycar = new Api();

        $vehicules = $pixmycar->vehicules('SelTotale', '-DateModifTh', 0, 1000, '*' );
        //$test = $pixmycar->select(3, "SelTotale");

        //$test = $pixmycar->select(3, "SelTotale", '-DateModifTh', 0, 10, "theme.IdTheme;theme.Critheme3;theme.TitreTheme;theme.Critheme6", '', ['Critheme10;C;4']);

        foreach ($vehicules as $vehicule) {

            dd($vehicule);
        }

        //$pixmycar->getVehicules($nb_enregistrement);
    }

    private function import()
    {


        $vehicules = Vehicule::query()->select(['*'])
            ->take(200)
            ->orderBy('DateModifTh', 'asc')
            ->publie()
            ->get();
        dump($vehicules->first());
        //$vehicules = Vehicule::query()->select(['theme.IdTheme', 'theme.Critheme3', 'theme.TitreTheme', 'theme.Critheme6'])->take(2)->orderBy('DateModifTh', 'desc')->get();

        foreach ($vehicules as $vehicule) {
            $this->info(PHP_EOL . 'Vehicule : '.$vehicule->Critheme3.' photos : ');


            $documents = Document::query()
                ->select(['*'])
                ->join($vehicule, $vehicule->id)
                ->orderBy('DateModifTh', 'desc')
                ->get();

            dd($documents->count(), $documents->first());
        }

    }



    protected function startProgressBar($max_steps)
    {
        $progress_bar = $this->output->createProgressBar($max_steps);
        $progress_bar->setFormat('custom');
        $progress_bar->setMessage('Start');
        $progress_bar->start();

        return $progress_bar;
    }

    protected function finishProgressBar($progress_bar)
    {
        $progress_bar->setMessage('Finish');
        $progress_bar->finish();
    }

}
