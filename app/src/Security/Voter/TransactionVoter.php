<?php

/**
 *Transaction Voter.
 */

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Transaction;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for transaction permissions.
 */
class TransactionVoter extends Voter
{
    public const VIEW = 'TRANSACTION_VIEW';
    public const EDIT = 'TRANSACTION_EDIT';
    public const DELETE = 'TRANSACTION_DELETE';

    /**
     * Checks if the attribute and subject are supported.
     *
     * @param string $attribute The attribute (VIEW, EDIT, DELETE)
     * @param mixed  $subject   The subject to secure (Transaction)
     *
     * @return bool True if supported
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Transaction;
    }

    /**
     * Votes on whether the user can perform the action.
     *
     * @param string         $attribute The attribute (VIEW, EDIT, DELETE)
     * @param mixed          $subject   The subject (Transaction)
     * @param TokenInterface $token     Security token
     *
     * @return bool True if access is granted
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = $subject;

        // Access is granted only if the user owns the related wallet
        return $transaction->getWallet()->getUser() === $user;
    }
}
