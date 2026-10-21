<?php

use APP\core\Application;
use APP\plugins\generic\scieloScreening\classes\ScreeningApiPolicy;
use APP\server\Server;
use APP\submission\Repository;
use APP\submission\Submission;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\Router;
use PHPUnit\Framework\TestCase;
use PKP\core\PKPPageRouter;
use PKP\core\PKPRequest;
use PKP\handler\PKPHandler;
use PKP\security\authorization\AuthorizationDecisionManager;
use PKP\security\authorization\AuthorizationPolicy;
use PKP\security\Role;
use PKP\user\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScreeningApiPolicyTest extends TestCase
{
    private $previousContainer;

    protected function setUp(): void
    {
        $this->previousContainer = Container::getInstance();
        $container = new Container();
        Container::setInstance($container);
        $container->instance('request', \Illuminate\Http\Request::create('/'));
        $container->instance('router', new Router(new Dispatcher($container), $container));
    }

    protected function tearDown(): void
    {
        Container::setInstance($this->previousContainer);
    }

    private function decide(int $role, int $submissionContext, array $stages = []): int
    {
        $context = new Server();
        $context->setId(1);
        $submission = new Submission();
        $submission->setId(42);
        $submission->setData('contextId', $submissionContext);
        $user = new User();
        $user->setId(7);

        $handler = $this->createMock(PKPHandler::class);
        $router = $this->createMock(PKPPageRouter::class);
        $router->method('getContext')->willReturn($context);
        $router->method('getRequestedOp')->willReturn('screening');
        $router->method('getHandler')->willReturn($handler);
        $request = $this->createMock(PKPRequest::class);
        $request->method('getRouter')->willReturn($router);
        $request->method('getContext')->willReturn($context);
        $request->method('getUser')->willReturn($user);
        $request->method('getUserVar')->with('submissionId')->willReturn('42');

        $repository = $this->createMock(Repository::class);
        $repository->expects($this->once())->method('get')->with(42)->willReturn($submission);
        Container::getInstance()->instance(Repository::class, $repository);
        $users = $this->createMock(\PKP\user\Repository::class);
        $users->method('getAccessibleWorkflowStages')->with(7, 1, $submission, [$role])->willReturn($stages);
        Container::getInstance()->instance(\PKP\user\Repository::class, $users);

        $args = [];
        $policies = (new ScreeningApiPolicy())->getPolicies($request, $args, [$role => ['screening']]);
        $manager = new AuthorizationDecisionManager();
        $manager->_authorizedContext[Application::ASSOC_TYPE_USER_ROLES] = [$role];
        foreach ($policies as $policy) {
            $manager->addPolicy($policy);
        }
        return $manager->decide();
    }

    public function testManagerCannotAccessAnotherContext(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->decide(Role::ROLE_ID_MANAGER, 2);
    }

    public function testUnassignedSubEditorIsDenied(): void
    {
        $this->assertSame(AuthorizationPolicy::AUTHORIZATION_DENY, $this->decide(Role::ROLE_ID_SUB_EDITOR, 1));
    }

    public function testAssignedSubEditorIsAllowed(): void
    {
        $this->assertSame(AuthorizationPolicy::AUTHORIZATION_PERMIT, $this->decide(Role::ROLE_ID_SUB_EDITOR, 1, [1 => [Role::ROLE_ID_SUB_EDITOR]]));
    }

    public function testManagerIsAllowedInTheirContext(): void
    {
        $this->assertSame(AuthorizationPolicy::AUTHORIZATION_PERMIT, $this->decide(Role::ROLE_ID_MANAGER, 1));
    }

    public function testSiteAdminIsAllowedInTheSelectedContext(): void
    {
        $this->assertSame(AuthorizationPolicy::AUTHORIZATION_PERMIT, $this->decide(Role::ROLE_ID_SITE_ADMIN, 1));
    }
}
