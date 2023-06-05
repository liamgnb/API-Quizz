<?php

namespace App\Controller;


use App\Dto\ThemeCountQuestionsDTO;
use App\Dto\ThemeDetailsQuestionsDTO;
use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class QuizzController extends AbstractController
{
    private ThemeRepository $themeRepository;
    private SerializerInterface $serializer;

    /**
     * @param ThemeRepository $themeRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(ThemeRepository $themeRepository, SerializerInterface $serializer)
    {
        $this->themeRepository = $themeRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/quizz/themes', name: 'api_getThemes', methods: ['GET'])]
    public function getAll(Request $request): Response
    {
        $slug = $request->get('theme');
        if ($slug != null) {
            return $this->getBySlug($slug);
        }

        $themes = [];

        foreach ($this->themeRepository->findBy([], ['libelle' => 'ASC']) as $theme)
        {
            $themeCountQuestionDTO = new ThemeCountQuestionsDTO();
            $themeCountQuestionDTO->setId($theme->getId());
            $themeCountQuestionDTO->setLibelle($theme->getLibelle());
            $themeCountQuestionDTO->setSlug($theme->getSlug());
            $themeCountQuestionDTO->setNbQuestions($theme->getQuestions()->count());
            $themes[] = $themeCountQuestionDTO;
        }

        $themesJson = $this->serializer->serialize($themes, 'json');

        return new Response($themesJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    public function getBySlug($slug): Response
    {

        $theme = $this->themeRepository->findOneBy(['slug' => $slug]);

        if (!$theme) {
            return $this->generateError("Le thème demandée n'existe pas.", Response::HTTP_NOT_FOUND);
        }

        $themeCountQuestionsDTO = new ThemeCountQuestionsDTO();
        $themeCountQuestionsDTO->setId($theme->getId());
        $themeCountQuestionsDTO->setLibelle($theme->getLibelle());
        $themeCountQuestionsDTO->setSlug($theme->getSlug());
        $themeCountQuestionsDTO->setNbQuestions($theme->getQuestions()->count());

        $themesJson = $this->serializer->serialize($themeCountQuestionsDTO, 'json');

        return new Response($themesJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/quizz/questions', name: 'api_getThemeBySlugWithQuestions', methods: ['GET'])]
    public function getBySlugWithQuestions(Request $request): Response
    {
        $slug = $request->get('theme');

        if ($slug == null) {
            return $this->generateError("Erreur d'URL.", Response::HTTP_NOT_FOUND);
        }

        $theme = $this->themeRepository->findOneBy(['slug' => $slug]);

        if (!$theme) {
            return $this->generateError("Le thème demandée n'existe pas.", Response::HTTP_NOT_FOUND);
        }

        foreach ($theme->getQuestions() as $question)
        {
            $themeDetailsQuestionsDTO = new ThemeDetailsQuestionsDTO();
            $themeDetailsQuestionsDTO->setId($question->getId());
            $themeDetailsQuestionsDTO->setLibelle($question->getLibelle());

            $reponses = [];
            foreach ($question->getReponses() as $reponse)
            {
                $reponses[] = ['id' => $reponse->getId(), 'libelle' => $reponse->getLibelle(), 'estCorrecte' => $reponse->isEstCorrecte()];
            }

            $themeDetailsQuestionsDTO->setReponses($reponses);
            $themes[] = $themeDetailsQuestionsDTO;

        }
        $themesJson = $this->serializer->serialize($themes, 'json');

        return new Response($themesJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/quizz/aleatoire', name: 'api_getThemeBySlugWithQuestionsRandom', methods: ['GET'])]
    public function getBySlugWithQuestionsRandom(Request $request): Response
    {
        $slug = $request->get('theme');
        $nb = $request->get('nb');

        if ($slug == null OR $nb == null OR $nb <= 0 OR !is_numeric($nb) ) {
            return $this->generateError("Erreur d'URL.", Response::HTTP_NOT_FOUND);
        }

        $theme = $this->themeRepository->findOneBy(['slug' => $slug]);

        if (!$theme) {
            return $this->generateError("Le thème demandée n'existe pas.", Response::HTTP_NOT_FOUND);
        }

        $questions = $theme->getQuestions();
        if (count($questions) < $nb) {
            return $this->generateError("Le nombre de questions demandé dépasse le nombre de questions disponible.", Response::HTTP_NOT_FOUND);
        }

        $historiqueQuestions = [];

        for ($i=1; $i<=$nb; $i++)
        {
            $position = random_int(0, count($questions) - 1);
            while (in_array($position, $historiqueQuestions)) {
                $position = random_int(0, count($questions) - 1);
            }
            $historiqueQuestions[] = $position;

            $themeDetailsQuestionsDTO = new ThemeDetailsQuestionsDTO();
            $themeDetailsQuestionsDTO->setId($questions[$position]->getId());
            $themeDetailsQuestionsDTO->setLibelle($questions[$position]->getLibelle());

            $reponses = [];
            foreach ($questions[$position]->getReponses() as $reponse)
            {
                $reponses[] = ['id' => $reponse->getId(), 'libelle' => $reponse->getLibelle(), 'estCorrecte' => $reponse->isEstCorrecte()];
            }

            $themeDetailsQuestionsDTO->setReponses($reponses);
            $themes[] = $themeDetailsQuestionsDTO;

        }
        $themesJson = $this->serializer->serialize($themes, 'json');

        return new Response($themesJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    /**
     * @param string $message
     * @param int $status
     * @return Response
     */
    private function generateError(string $message, int $status) : Response {
        // Créer un tableau associatif correspondant à l'erreur
        $erreur = [
            'status' => $status,
            'message' => $message
        ];

        // Renvoyer la réponse au format JSON, avec le tableau $erreur
        return new Response(json_encode($erreur), Response::HTTP_NOT_FOUND,
            ['content-type' => 'application/json']
        );
    }

}
