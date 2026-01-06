<?php
/**
 * Classe Utilisateur - Gestion des utilisateurs
 * Authentification et autorisation
 */

class Utilisateur extends Model
{
    protected string $table = 'utilisateurs';

    /**
     * Trouver un utilisateur par nom d'utilisateur
     */
    public function findByUsername(string $username): ?array
    {
        // 1. Essayer de trouver par username direct
        $sql = "SELECT * FROM {$this->table} WHERE username = ? LIMIT 1";
        $user = $this->db->fetchOne($sql, [$username]);
        
        if ($user) {
            return $user;
        }

        // 2. Essayer de trouver par téléphone via la table membres
        $sql = "SELECT u.* FROM {$this->table} u 
                JOIN membres m ON u.id = m.user_id 
                WHERE m.telephone = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$username]);
    }

    /**
     * Vérifier le mot de passe
     */
    public function verifierMotDePasse(string $password, string $hash): bool
    {
        return Security::verifyPassword($password, $hash);
    }

    /**
     * Vérifier si l'utilisateur peut modifier
     */
    public function peutModifier(string $role): bool
    {
        return in_array($role, ['admin', 'comptable'], true);
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function createUser(string $username, string $password, string $role = 'membre'): int
    {
        $data = [
            'username' => $username,
            'password' => Security::hashPassword($password),
            'role' => $role
        ];
        return $this->create($data);
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->update($id, [
            'password' => Security::hashPassword($newPassword)
        ]);
    }

    /**
     * Obtenir tous les utilisateurs par rôle
     */
    public function findByRole(string $role): array
    {
        return $this->findAll(['role' => $role]);
    }
}
