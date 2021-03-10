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
                                {--identifiant= : Récupère un seul véhicule. XX-22-XX}
                                {--date= : Récupère uniquement les véhicules modifiés à partir de cette date. Format d/m/Y.}
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

        $cache_date = $this->option('date') ? Carbon::createFromFormat('d/m/Y', $this->option('date')) : Cache::get('pixmycar_cache_date');

        if ($this->option('no-cache') and !$this->option('date')) {
            $cache_date = null;
        }

        $selection_vehicules = null;
        if (!$this->option('identifiant')) {
            $selection_vehicules = config('pixmycar.model_vehicule.class')::whereNotNull(config('pixmycar.model_vehicule.identifiant'))->get()->pluck(config('pixmycar.model_vehicule.identifiant'))->toArray();
        }

        if ($this->option('identifiant')) {
            $selection_vehicules = [$this->option('identifiant')];
        }

        $page = 1;
        $nb_enregistrement = 1000;

        do {

            $query = Vehicule::query()
                ->select(['theme.IdTheme', 'theme.Critheme3', 'theme.TitreTheme', 'theme.Critheme6', 'Critheme10'])
                ->orderBy('DateModifTh', 'desc')
                ->page($page)
                ->take($nb_enregistrement);


            if ($selection_vehicules) {
                $query->identifiants($selection_vehicules);
            }

            if ($cache_date) {
                $query->where('DateModifTh', '>=', $cache_date->format(Element::DATE_FORMAT));
            }

            $vehicules = $query->publie()->get();

            $progress_bar->setMaxSteps($vehicules->count());

            //dd($vehicules->first());

            foreach ($vehicules as $vehicule) {
                $progress_bar->setMessage('Vehicule : ' . $vehicule->Critheme3 . ' photos');
                $progress_bar->advance();

                $documents = $this->getDocuments($vehicule);

                $vehicule_model = config('pixmycar.model_vehicule.class')::where(config('pixmycar.model_vehicule.identifiant'), $vehicule->identifiant)
                    ->with(['images' => function($query) {
                        $query->where('groupe', 'pixmycar');
                    }])
                    ->first();

                if (!$vehicule_model) {
                    continue;
                }

                $vehicule_model->pixmycar_player = $vehicule->player;

                $ids = [];
                foreach ($documents as $document) {

                    if (!$document->is_image) {
                        continue;
                    }

                    $media = $vehicule_model->images->where('fichier', $document->fichier)->first();

                    if ($media) {
                        if (File::exists($media->path)) {
                            \Croppa::delete($media->cropPath);
                        }
                    } else {
                        // Enregistrement en bdd
                        $media = new Media;
                        $media->titre = $document->Mot1;
                        $media->fichier = $document->fichier;
                        $media->type = Media::TYPE_IMAGE;
                        $media->repertoire = 'pixmycar';
                        $media->publication_id = $vehicule_model->id;
                        $media->publication_type = config('pixmycar.model_vehicule.class');
                        $media->groupe = 'pixmycar';
                        $media->save();

                    }

                    $fichier = file_get_contents($document->url);
                    file_put_contents(public_path(config('ipsum.media.path').'pixmycar/'.$document->fichier), $fichier);

                    $ids[] = $document->fichier;
                }

                // Suppression des medias qui n'existe plus
                foreach ($vehicule_model->images->whereNotIn('fichier', $ids) as $media) {
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
            Cache::put('pixmycar_cache_date', Carbon::now());
        }

        $this->finishProgressBar($progress_bar);
        $this->info(PHP_EOL . '**** Fin import ****');
    }

    protected function getDocuments(Vehicule $vehicule): Collection
    {
        return Document::query()
            ->select(['IdDocument', 'MotsClesImage1', 'Critere6', 'Reference'])
            ->join($vehicule, $vehicule->id)
            ->orderBy('DateModifTh', 'desc')
            ->get()
            ->sortBy(function ($document, $key) {
                return $document->is_illustration !== true;
            });
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
