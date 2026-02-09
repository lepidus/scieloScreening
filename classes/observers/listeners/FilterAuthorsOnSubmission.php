<?php

namespace APP\plugins\generic\scieloScreening\classes\observers\listeners;

use Illuminate\Events\Dispatcher;
use PKP\observers\events\SubmissionSubmitted;
use APP\facades\Repo;
use APP\plugins\generic\scieloScreening\classes\ScieloScreeningDAO;

class FilterAuthorsOnSubmission
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(
            SubmissionSubmitted::class,
            FilterAuthorsOnSubmission::class
        );
    }

    public function handle(SubmissionSubmitted $event): void
    {
        $submission = $event->submission;
        $contextId = $event->context->getId();
        $publication = $submission->getCurrentPublication();

        $scieloScreeningDao = new ScieloScreeningDAO();
        $scieloJournalUserGroupId = $scieloScreeningDao->getScieloJournalUserGroupId($contextId);

        foreach ($publication->getData('authors') as $author) {
            $user = Repo::user()->getByEmail($author->getData('email'));
            if (is_null($user)) {
                continue;
            }

            if ($scieloScreeningDao->userIsInUserGroup($user->getId(), $scieloJournalUserGroupId)) {
                Repo::author()->delete($author);
            }
        }
    }
}
