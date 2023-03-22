<?php

namespace App\Dto;

class ThemeDetailsQuestionsDTO
{
    private int $id;
    private string $libelle;
    private array $reponses;

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
     * @return array
     */
    public function getReponses(): array
    {
        return $this->reponses;
    }

    /**
     * @param array $reponses
     */
    public function setReponses(array $reponses): void
    {
        $this->reponses = $reponses;
    }




}