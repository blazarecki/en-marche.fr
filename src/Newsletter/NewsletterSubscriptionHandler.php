<?php

namespace AppBundle\Newsletter;

use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\NewsletterSubscriptionMessage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class NewsletterSubscriptionHandler
{
    private $manager;
    private $mailjet;
    private $requestStack;

    public function __construct(EntityManager $manager, MailjetService $mailjet, RequestStack $requestStack)
    {
        $this->manager = $manager;
        $this->mailjet = $mailjet;
        $this->requestStack = $requestStack;
    }

    public function subscribe(NewsletterSubscription $subscription)
    {
        $subscription = $this->recoverSoftDeletedSubscription($subscription);

        $this->manager->persist($subscription);
        $this->manager->flush();

        $campaignExpired = (bool) $this->requestStack->getCurrentRequest()->attributes->get('_campaign_expired', false);
        if (!$campaignExpired) {
            $this->mailjet->sendMessage(NewsletterSubscriptionMessage::createFromSubscription($subscription));
        }
    }

    public function unsubscribe(?string $email)
    {
        $subscription = $this->findSubscriptionByEmail($email);

        if ($subscription) {
            $this->manager->remove($subscription);
            $this->manager->flush();
        }
    }

    private function recoverSoftDeletedSubscription(NewsletterSubscription $subscription): NewsletterSubscription
    {
        $this->manager->getFilters()->disable('softdeleteable');

        $softDeletedSubscription = $this->findSubscriptionByEmail($subscription->getEmail());

        $this->manager->getFilters()->enable('softdeleteable');

        if (!$softDeletedSubscription) {
            return $subscription;
        }

        if ($postalCode = $subscription->getPostalCode()) {
            $softDeletedSubscription->setPostalCode($postalCode);
        }

        $softDeletedSubscription->recover();

        return $softDeletedSubscription;
    }

    private function findSubscriptionByEmail(?string $email): ?NewsletterSubscription
    {
        return $this
            ->manager
            ->getRepository(NewsletterSubscription::class)
            ->findOneBy(['email' => $email]);
    }
}
