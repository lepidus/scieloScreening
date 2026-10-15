<?php

namespace APP\plugins\generic\scieloScreening\classes;

use PKP\core\PKPRequest;
use PKP\plugins\interfaces\HasAuthorizationPolicy;
use PKP\security\authorization\SubmissionAccessPolicy;

class ScreeningApiPolicy implements HasAuthorizationPolicy
{
    public function getPolicies(PKPRequest $request, array &$args, array $roleAssignments): array
    {
        return [new SubmissionAccessPolicy($request, $args, $roleAssignments)];
    }
}
