<?php

namespace App\Dto;

class ThemeCountQuestionsDTO
{
    private int $id;
    private string $libelle;
    private string $slug;
    private int $nbQuestions;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @param string $libelle
     */
    public function setLibelle(string $libelle): void
    {
        $this->libelle = $libelle;
    }

    /**
     * @return int
     */
    public function getNbQuestions(): int
    {
        return $this->nbQuestions;
    }

    /**
     * @param int $nbQuestions
     */
    public function setNbQuestions(int $nbQuestions): void
    {
        $this->nbQuestions = $nbQuestions;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

}