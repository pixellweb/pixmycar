<?php

namespace Citadelle\PixMyCar\app\Console\Commands;


use Carbon\Carbon;
use Citadelle\PixMyCar\app\Api;
use Citadelle\PixMyCar\app\Document;
use Citadelle\PixMyCar\app\Element;
use Citadelle\PixMyCar\app\Vehicule;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Ipsum\Media\app\Models\Media;
use Symfony\Component\Console\Helper\ProgressBar;
use Illuminate\Support\Facades\Cache;

class Import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pixmycar
                                {--no-cache : Suppression du système de cache.}
                                {--identifiant=XX-22-XX : Récupère un seul véhicule.}
                                {--date : Récupère uniquement les véhicules modifiés à partir de cette date. Format d/m/Y.}
                                {--debug : Show process output or not. Useful for debugging.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import des photos pixmycar et du lecteur 360';


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
        $this->info(PHP_EOL . '**** Import pixmycar ****');
        $progress_bar = $this->startProgressBar(0);
        $progress_bar->setMessage('Import des véhicules');

        $cache_date = Cache::get('pixmycar_cache_date');

        if ($this->option('no-cache')) {
            $cache_date = null;
        }

        $selection_vehicules = null;
        if (!$cache_date) {
            $selection_vehicules = config('pixmycar.model_vehicule.class')::all()->pluck(config('pixmycar.model_vehicule.identifiant'))->toArray();
        }

        $page = 1;
        $nb_enregistrement = 1000;

        do {

            $query = Vehicule::query()
                ->select(['theme.IdTheme', 'theme.Critheme3', 'theme.TitreTheme', 'theme.Critheme6', 'Critheme10'])
                ->orderBy('DateModifTh', 'desc')
                ->page($page)
                ->take($nb_enregistrement);

            if ($this->option('identifiant')) {
                $query->identifiants([$this->option('identifiant')])->publie();
            } elseif ($selection_vehicules) {
                // Pas possible rechercher uniquement les publiés à cause des OU
                $query->identifiants($selection_vehicules);
            } else {
                $query->where('DateModifTh', '>=', $cache_date->format(Element::DATE_FORMAT))->publie();
            }

            $vehicules = $query->get();


            $progress_bar->advance();
            $progress_bar->setMaxSteps($vehicules->count());

            //dump($vehicules->first());

            foreach ($vehicules as $vehicule) {
                $progress_bar->setMessage(PHP_EOL . 'Vehicule : ' . $vehicule->Critheme3 . ' photos');
                $progress_bar->advance();

                $documents = $this->getDocuments($vehicule);

                $vehicule_model = config('pixmycar.model_vehicule.class')::where(config('pixmycar.model_vehicule.identifiant'), $vehicule->identifiant)->with('images')->fitst();

                if (!$vehicule_model) {
                    continue;
                }

                $vehicule_model->pixmycar_player = $vehicule->player;

                $ids = [];
                foreach ($documents as $document) {

                    if (!$document->is_image) {
                        continue;
                    }

                    if ($document->is_illustration) {
                        $vehicule_model->pixmycar_preview = $document->preview;
                    }

                    $media = $vehicule_model->images->where('fichier', $document->id)->first();

                    if ($media) {
                        if (File::exists($media->path)) {
                            \Croppa::delete($media->cropPath);
                        }
                    } else {
                        // Enregistrement en bdd
                        $media = new Media;
                        $media->titre = 'TODO';
                        $media->fichier = $document->id;
                        $media->type = Media::TYPE_IMAGE;
                        $media->repertoire = 'pixmycar';
                        $media->publication_id = $vehicule_model->id;
                        $media->publication_type = config('pixmycar.model_vehicule.class');
                        $media->groupe = 'pixmycar';
                        $media->save();
                    }

                    $fichier = file_get_contents($document->url);
                    file_put_contents(config('ipsum.media.path').'/pixmycar/'.$document->id.'.jpg', $fichier);

                    $ids[] = $document->id;
                }

                // Suppression des medias qui n'existe plus
                foreach ($vehicule_model->medias->whereNotIn('fichier', $ids)->get() as $media) {
                    $media->delete();
                    if (File::exists($media->path)) {
                        \Croppa::delete($media->cropPath);
                    }
                }

                $vehicule_model->save();

            }

            $page++;

        } while ($vehicules->count() == $nb_enregistrement);

        if (!$this->option('no-cache')) {
            Cache::put(Carbon::now(), 'value');
        }

        $this->finishProgressBar($progress_bar);
        $this->info(PHP_EOL . '**** Fin import ****');
    }

    protected function getDocuments(Vehicule $vehicule): Collection
    {
        return Document::query()
            ->select(['IdDocument', 'Critere6', 'MotsClesImage1', 'Reference', 'Mot1'])
            ->join($vehicule, $vehicule->id)
            ->orderBy('DateModifTh', 'desc')
            ->get()
            ->sortByDesc('is_illustration');
    }

    private function test()
    {

        //$documents = Document::query()->select(['*'])->where('Critere3', "ET-296-QB")->orderBy('DateModifTh', 'desc')->take(1)->get();
        $documents = Document::query()->select(['*'])->where('TypeFichierPpal', 'Plate-forme Video')->orderBy('DateModifTh', 'desc')->take(1)->get();
        dd($documents);

        $vehicules = Vehicule::query()->select(['theme.IdTheme', 'theme.Critheme3', 'theme.TitreTheme', 'theme.Critheme6'])->take(2)->orderBy('DateModifTh', 'desc')->where('Critheme2', 'Land Cruiser')->get();

        dd($vehicules->first());

        $pixmycar = new Api();

        $vehicules = $pixmycar->vehicules('SelTotale', '-DateModifTh', 0, 1000, '*');
        //$test = $pixmycar->select(3, "SelTotale");

        //$test = $pixmycar->select(3, "SelTotale", '-DateModifTh', 0, 10, "theme.IdTheme;theme.Critheme3;theme.TitreTheme;theme.Critheme6", '', ['Critheme10;C;4']);

        foreach ($vehicules as $vehicule) {

            dd($vehicule);
        }

        //$pixmycar->getVehicules($nb_enregistrement);
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
