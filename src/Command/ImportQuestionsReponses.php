<?php

namespace App\Command;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use App\Repository\ThemeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:import-questions-reponses')]
class ImportQuestionsReponses extends Command
{
    protected static $defaultDescription = 'Importation des questions et réponses.';
    private QuestionRepository $questionRepository;
    private ReponseRepository $reponseRepository;
    private ThemeRepository $themeRepository;

    /**
     * @param QuestionRepository $questionRepository
     * @param ReponseRepository $reponseRepository
     * @param ThemeRepository $themeRepository
     */
    public function __construct(QuestionRepository $questionRepository, ReponseRepository $reponseRepository, ThemeRepository $themeRepository)
    {
        $this->questionRepository = $questionRepository;
        $this->reponseRepository = $reponseRepository;
        $this->themeRepository = $themeRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Vidé les tables
        $this->reponseRepository->deleteAll();
        $this->questionRepository->deleteAll();

        // Récupération du fichier CSV
        $reader = Reader::createFromPath('./src/csv/questions_reponses.csv', 'r');
        $reader->setHeaderOffset(0);
        $reader->setDelimiter(';');
        $records = $reader->getRecords();

        // progress bar
        $progressBar = new ProgressBar($output, count($reader));
        $progressBar->start();

        foreach ($records as $offset => $record) {
            // Question
            $question = new Question();
            $question->setLibelle($record['Question']);
            $question->setTheme($this->themeRepository->findOneBy(['libelle' => $record['Theme']]));
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
        $output->writeln('<info>Questions et réponses ajouté avec succes.</info>');
        return Command::SUCCESS;
    }
}