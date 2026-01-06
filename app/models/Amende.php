<?php
/**
 * Classe Amende - Gestion des amendes
 * Calcul des pénalités pour retards de paiement
 */

class Amende
{
    public const MONTANT_PAR_MOIS = 2000;

    private int $moisRetard;
    private float $montantTotal;

    /**
     * Calculer le montant de l'amende
     */
    public function calculer(int $mois): float
    {
        $this->moisRetard = $mois;
        $this->montantTotal = $mois * self::MONTANT_PAR_MOIS;
        return $this->montantTotal;
    }

    /**
     * Obtenir le montant par mois
     */
    public static function getMontantParMois(): float
    {
        return self::MONTANT_PAR_MOIS;
    }
}
