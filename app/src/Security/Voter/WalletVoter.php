<?php

/**
 * Wallet Voter.
 */

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Wallet;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for wallet permissions.
 */
class WalletVoter extends Voter
{
    public const VIEW = 'WALLET_VIEW';
    public const EDIT = 'WALLET_EDIT';
    public const DELETE = 'WALLET_DELETE';

    /**
     * Checks if the attribute and subject are supported.
     *
     * @param string $attribute The action (VIEW, EDIT, DELETE)
     * @param mixed  $subject   The object being secured (Wallet)
     *
     * @return bool True if supported
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Wallet;
    }

    /**
     * Votes on whether the user can perform the given action.
     *
     * @param string         $attribute The action to check
     * @param mixed          $subject   The object (Wallet)
     * @param TokenInterface $token     The security token
     *
     * @return bool True if access is granted
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Wallet $wallet */
        $wallet = $subject;

        // Only the wallet owner can view/edit/delete
        return $wallet->getUser() === $user;
    }
}
