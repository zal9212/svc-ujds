<?php
/**
 * Tests de validation des calculs
 * Vérifier que les calculs correspondent exactement aux spécifications
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Membre.php';
require_once __DIR__ . '/../app/models/Versement.php';
require_once __DIR__ . '/../app/models/Avance.php';
require_once __DIR__ . '/../app/models/Amende.php';

class CalculationTests
{
    private Membre $membreModel;
    private int $passedTests = 0;
    private int $failedTests = 0;
    private array $errors = [];

    public function __construct()
    {
        $this->membreModel = new Membre();
    }

    /**
     * Exécuter tous les tests
     */
    public function runAll(): void
    {
        echo "=== TESTS DE VALIDATION DES CALCULS ===\n\n";

        $this->testAmendeConstante();
        $this->testMembreActif();
        $this->testMembreVG();
        $this->testMoisRetard();
        $this->testMontantDu();
        $this->testAvances();

        $this->displayResults();
    }

    /**
     * Test: Constante amende
     */
    private function testAmendeConstante(): void
    {
        $amende = new Amende();
        $result = $amende->calculer(3);
        
        $this->assert(
            $result === 6000.0,
            "Amende pour 3 mois = 6000 FCFA",
            "Attendu: 6000, Obtenu: $result"
        );
    }

    /**
     * Test: Membre ACTIF avec retards
     */
    private function testMembreActif(): void
    {
        // Créer un membre de test
        $membre = [
            'id' => 999,
            'code' => 'TEST001',
            'designation' => 'Test Membre Actif',
            'montant_mensuel' => 50000,
            'statut' => 'ACTIF',
            'versements' => [
                ['statut' => 'EN_ATTENTE', 'montant' => 0],
                ['statut' => 'EN_ATTENTE', 'montant' => 0],
                ['statut' => 'PAYE', 'montant' => 50000],
            ],
            'avances' => []
        ];

        $moisRetard = $this->membreModel->calculerMoisRetard($membre);
        $this->assert(
            $moisRetard === 2,
            "Membre ACTIF: 2 mois en retard",
            "Attendu: 2, Obtenu: $moisRetard"
        );

        $amende = $this->membreModel->calculerAmende($membre);
        $this->assert(
            $amende === 4000.0,
            "Membre ACTIF: Amende = 4000 FCFA",
            "Attendu: 4000, Obtenu: $amende"
        );

        $totalVerse = $this->membreModel->totalVerse($membre);
        $this->assert(
            $totalVerse === 50000.0,
            "Membre ACTIF: Total versé = 50000 FCFA",
            "Attendu: 50000, Obtenu: $totalVerse"
        );

        $montantDu = $this->membreModel->montantDu($membre);
        // (2 × 50000) + 4000 - 50000 = 54000
        $this->assert(
            $montantDu === 54000.0,
            "Membre ACTIF: Montant dû = 54000 FCFA",
            "Attendu: 54000, Obtenu: $montantDu"
        );
    }

    /**
     * Test: Membre VG (tous calculs à zéro)
     */
    private function testMembreVG(): void
    {
        $membre = [
            'id' => 998,
            'code' => 'TEST002',
            'designation' => 'Test Membre VG',
            'montant_mensuel' => 50000,
            'statut' => 'VG',
            'versements' => [
                ['statut' => 'EN_ATTENTE', 'montant' => 0],
                ['statut' => 'EN_ATTENTE', 'montant' => 0],
            ],
            'avances' => []
        ];

        $this->assert(
            $this->membreModel->estVG($membre) === true,
            "Membre VG: estVG() = true",
            "Devrait être VG"
        );

        $this->assert(
            $this->membreModel->calculerMoisRetard($membre) === 0,
            "Membre VG: Mois retard = 0",
            "VG ne doit pas avoir de retard"
        );

        $this->assert(
            $this->membreModel->calculerAmende($membre) === 0.0,
            "Membre VG: Amende = 0",
            "VG ne doit pas avoir d'amende"
        );

        $this->assert(
            $this->membreModel->montantDu($membre) === 0.0,
            "Membre VG: Montant dû = 0",
            "VG ne doit rien"
        );
    }

    /**
     * Test: Calcul mois en retard
     */
    private function testMoisRetard(): void
    {
        $membre = [
            'statut' => 'ACTIF',
            'versements' => [
                ['statut' => 'EN_ATTENTE'],
                ['statut' => 'PAYE'],
                ['statut' => 'EN_ATTENTE'],
                ['statut' => 'PARTIEL'],
                ['statut' => 'EN_ATTENTE'],
                ['statut' => 'ANNULE'],
            ]
        ];

        $moisRetard = $this->membreModel->calculerMoisRetard($membre);
        $this->assert(
            $moisRetard === 3,
            "Calcul mois retard: 3 EN_ATTENTE",
            "Attendu: 3, Obtenu: $moisRetard"
        );
    }

    /**
     * Test: Montant dû avec avances
     */
    private function testMontantDu(): void
    {
        $membre = [
            'montant_mensuel' => 50000,
            'statut' => 'ACTIF',
            'versements' => [
                ['statut' => 'EN_ATTENTE', 'montant' => 0],
                ['statut' => 'EN_ATTENTE', 'montant' => 0],
                ['statut' => 'PARTIEL', 'montant' => 30000],
            ],
            'avances' => [
                ['montant' => 20000]
            ]
        ];

        $montantDu = $this->membreModel->montantDu($membre);
        // (2 × 50000) + 4000 - 30000 - 20000 = 54000
        $this->assert(
            $montantDu === 54000.0,
            "Montant dû avec avance: 54000 FCFA",
            "Attendu: 54000, Obtenu: $montantDu"
        );
    }

    /**
     * Test: Avances
     */
    private function testAvances(): void
    {
        $membre = [
            'statut' => 'ACTIF',
            'versements' => [],
            'avances' => [
                ['montant' => 50000],
                ['montant' => 30000],
                ['montant' => 20000]
            ]
        ];

        $totalAvance = $this->membreModel->totalAvance($membre);
        $this->assert(
            $totalAvance === 100000.0,
            "Total avances: 100000 FCFA",
            "Attendu: 100000, Obtenu: $totalAvance"
        );
    }

    /**
     * Assertion helper
     */
    private function assert(bool $condition, string $testName, string $errorMessage = ''): void
    {
        if ($condition) {
            $this->passedTests++;
            echo "✓ PASS: $testName\n";
        } else {
            $this->failedTests++;
            echo "✗ FAIL: $testName\n";
            if ($errorMessage) {
                echo "  → $errorMessage\n";
                $this->errors[] = "$testName: $errorMessage";
            }
        }
    }

    /**
     * Afficher les résultats
     */
    private function displayResults(): void
    {
        echo "\n=== RÉSULTATS ===\n";
        echo "Tests réussis: {$this->passedTests}\n";
        echo "Tests échoués: {$this->failedTests}\n";
        
        if ($this->failedTests > 0) {
            echo "\n=== ERREURS ===\n";
            foreach ($this->errors as $error) {
                echo "- $error\n";
            }
        }

        $total = $this->passedTests + $this->failedTests;
        $percentage = $total > 0 ? round(($this->passedTests / $total) * 100, 2) : 0;
        echo "\nTaux de réussite: $percentage%\n";
    }
}

// Exécuter les tests si appelé directement
if (php_sapi_name() === 'cli') {
    $tests = new CalculationTests();
    $tests->runAll();
}
