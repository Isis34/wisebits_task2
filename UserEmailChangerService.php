<?php

class UserEmailChangerService
{
    private \PDO $db;

    private UserEmailSenderInterface $userEmailSender;

    public function __construct(\PDO $db, UserEmailSenderInterface $userEmailSender )
    {
        $this->db = $db;
        $this->userEmailSender = $userEmailSender;
    }

    /**
     * @param int $userId
     * @param string $email
     *
     * @return void
     *
     * @throws \PDOException
     */
    public function changeEmail(int $userId, string $email): void
    {
        $this->db->beginTransaction();

        $statement = $this->db->prepare('SELECT email FROM users WHERE id = :id FOR UPDATE');
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
        $oldEmail = $statement->fetch(PDO::FETCH_ASSOC)['email'] ?? null;

        $statement = $this->db->prepare('UPDATE users SET email = :email WHERE id = :id');
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->execute();

        $this->db->commit();

        if ($oldEmail) {
            $this->userEmailSender->sendEmailChangedNotification($oldEmail, $email);
        }
    }
}