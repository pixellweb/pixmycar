<?php

namespace Citadelle\PixMyCar\app\Console\Commands;


use Citadelle\PixMyCar\app\Vehicule;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pixmycar:test';

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
        //$documents = Document::query()->select(['*'])->where('Critere3', "ET-296-QB")->orderBy('DateModifTh', 'desc')->take(1)->get();
        $documents = Vehicule::query()
            ->select(['Critheme3', 'Critheme10'])

            ->orWhere('Critheme3', "DK-003-EP")->orWhere('Critheme3', "FM-883-LF")->orWhere('Critheme3', "DS-952-ZH")->orWhere('Critheme3', "FK-745-PF")
            ->where('Critheme10', '4 - Publié')

            ->orderBy('DateModifTh', 'desc')
            ->take(10)
            ->get();
        dd($documents);
    }



}
