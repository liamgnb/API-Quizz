<?php

namespace App\Command;

use App\Entity\Theme;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\ThemeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\String\Slugger\SluggerInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:import-themes')]
class ImportThemes extends Command
{
    protected static $defaultDescription = 'Importation des questions et réponses.';
    private ThemeRepository $themeRepository;
    private QuestionRepository $questionRepository;
    private ReponseRepository $reponseRepository;
    private SluggerInterface $slugger;

    /**
     * @param ThemeRepository $themeRepository
     * @param QuestionRepository $questionRepository
     * @param ReponseRepository $reponseRepository
     * @param SluggerInterface $slugger
     */
    public function __construct(ThemeRepository $themeRepository, QuestionRepository $questionRepository, ReponseRepository $reponseRepository, SluggerInterface $slugger)
    {
        $this->themeRepository = $themeRepository;
        $this->questionRepository = $questionRepository;
        $this->reponseRepository = $reponseRepository;
        $this->slugger = $slugger;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Vidé les tables
        $output->writeln('');
        $output->writeln('<info>Suppression des données...</info>');
        $this->reponseRepository->deleteAll();
        $this->questionRepository->deleteAll();
        $this->themeRepository->deleteAll();

        // Récupération du fichier CSV
        $reader = Reader::createFromPath('./src/csv/themes.csv', 'r');
        $reader->setHeaderOffset(0);
        $reader->setDelimiter(';');
        $records = $reader->getRecords();

        // progress bar
        $progressBar = new ProgressBar($output, count($reader));
        $progressBar->start();

        foreach ($records as $offset => $record) {
            // Theme
            $theme = new Theme();
            $theme->setLibelle($record['Libelle']);
            $theme->setSlug($this->slugger->slug($theme->getLibelle())->lower());
            $this->themeRepository->save($theme, true);

            $progressBar->advance();
        }

        // Affichage Progress Bar et message de fin
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Thèmes ajouté avec succes. (Ajouter les questions et réponses à l\'aide de la commande : symfony console app:import-questions-reponses</info>');
        return Command::SUCCESS;
    }
}