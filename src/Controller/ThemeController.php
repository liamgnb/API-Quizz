<?php

namespace App\Controller;


use App\Dto\ThemeCountQuestionsDTO;
use App\Dto\ThemeDetailsQuestionsDTO;
use App\Repository\ThemeRepository;
use Proxies\__CG__\App\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ThemeController extends AbstractController
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

    #[Route('/api/themes', name: 'app_getThemes', methods: ['GET'])]
    public function getAll(): Response
    {
        $themes = [];

        foreach ($this->themeRepository->findAll() as $theme)
        {
            $themeCountQuestionDTO = new ThemeCountQuestionsDTO();
            $themeCountQuestionDTO->setId($theme->getId());
            $themeCountQuestionDTO->setLibelle($theme->getLibelle());
            $themeCountQuestionDTO->setNbQuestions($theme->getQuestions()->count());
            $themes[] = $themeCountQuestionDTO;
        }

        $themesJson = $this->serializer->serialize($themes, 'json');

        return new Response($themesJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/themes/{id}', name: 'api_getThemeById', methods: ['GET'])]
    public function getById($id): Response
    {
        $theme = $this->themeRepository->findOneBy(['id' => $id]);

        if (!$theme) {
            return $this->generateError("Le thème demandée n'existe pas.", Response::HTTP_NOT_FOUND);
        }

        $themeCountQuestionsDTO = new ThemeCountQuestionsDTO();
        $themeCountQuestionsDTO->setId($theme->getId());
        $themeCountQuestionsDTO->setLibelle($theme->getLibelle());
        $themeCountQuestionsDTO->setNbQuestions($theme->getQuestions()->count());

        $themesJson = $this->serializer->serialize($themeCountQuestionsDTO, 'json');

        return new Response($themesJson, Response::HTTP_OK, ['content-type' => 'application/json']);
    }

    #[Route('/api/themes/{id}/questions', name: 'api_getThemeByIdWithQuestions', methods: ['GET'])]
    public function getByIdWithPosts($id): Response
    {
        $theme = $this->themeRepository->findOneBy(['id' => $id]);

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
