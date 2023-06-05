<?php

namespace App\Command;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Theme;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\ThemeRepository;
use League\Csv\Reader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:import-data')]
class ImportData extends Command
{
    protected static $defaultDescription = 'Importation des thèmes, questions et réponses.';
    private QuestionRepository $questionRepository;
    private ReponseRepository $reponseRepository;
    private ThemeRepository $themeRepository;
    private SluggerInterface $slugger;

    /**
     * @param QuestionRepository $questionRepository
     * @param ReponseRepository $reponseRepository
     * @param ThemeRepository $themeRepository
     * @param SluggerInterface $slugger
     */
    public function __construct(QuestionRepository $questionRepository, ReponseRepository $reponseRepository, ThemeRepository $themeRepository, SluggerInterface $slugger)
    {
        $this->questionRepository = $questionRepository;
        $this->reponseRepository = $reponseRepository;
        $this->themeRepository = $themeRepository;
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
        $reader = Reader::createFromPath('./src/csv/donnees.csv', 'r');
        $reader->setHeaderOffset(0);
        $reader->setDelimiter(';');
        $records = $reader->getRecords();

        // progress bar
        $output->writeln('');
        $output->writeln('<info>Ajout des données...</info>');
        $progressBar = new ProgressBar($output, count($reader));
        $progressBar->start();

        foreach ($records as $record) {
            // Theme
            $theme = $this->themeRepository->findOneBy(['libelle' => $record['Theme']]);
            if (!$theme) {
                $theme = new Theme();
                $theme->setLibelle($record['Theme']);
                $theme->setSlug($this->slugger->slug($theme->getLibelle())->lower());
                $this->themeRepository->save($theme, true);
            }

            // Question
            $question = new Question();
            $question->setLibelle($record['Question']);
            $question->setTheme($theme);
            $this->questionRepository->save($question, true);

            // Reponse
            foreach (['Reponse_Correcte' => true, 'Reponse_Incorrecte_1' => false,
                         'Reponse_Incorrecte_2' => false, 'Reponse_Incorrecte_3' => false] as $noReponse => $estCorrecte){
                $reponse = new Reponse();
                $reponse->setLibelle($record[$noReponse]);
                $reponse->setEstCorrecte($estCorrecte);
                $reponse->setQuestion($question);
                $this->reponseRepository->save($reponse, true);
            }

            $progressBar->advance();
        }

        // Affichage Progress Bar et message de fin
        $progressBar->finish();
        $output->writeln('');
        $output->writeln('<info>Thèmes, questions et réponses ajouté avec succes.</info>');
        return Command::SUCCESS;
    }
}