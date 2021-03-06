<?php

namespace Oro\Bundle\CheckoutBundle\Tests\Unit\Provider;

use Oro\Bundle\CheckoutBundle\Handler\ForgotPasswordHandler;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Form\Handler\CustomerUserPasswordRequestHandler;
use Oro\Bundle\CustomerBundle\Layout\DataProvider\FrontendCustomerUserFormProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class ForgotPasswordHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerUserPasswordRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordRequestHandler;

    /**
     * @var FrontendCustomerUserFormProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerUserFormProvider;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var ForgotPasswordHandler
     */
    private $forgotPasswordHandler;

    protected function setUp()
    {
        $this->passwordRequestHandler = $this->createMock(CustomerUserPasswordRequestHandler::class);
        $this->customerUserFormProvider = $this->createMock(FrontendCustomerUserFormProvider::class);
        $this->session = $this->createMock(Session::class);

        $this->forgotPasswordHandler = new ForgotPasswordHandler(
            $this->passwordRequestHandler,
            $this->customerUserFormProvider,
            $this->session
        );
    }

    public function testHandleWithGetMethod()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $this->assertFalse($this->forgotPasswordHandler->handle($request));
    }

    public function testHandleWithoutParameter()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $this->assertFalse($this->forgotPasswordHandler->handle($request));
    }

    public function testHandleWithoutUser()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->query->add(['isForgotPassword' => true]);

        $form = $this->createMock(FormInterface::class);
        $this->customerUserFormProvider->expects($this->once())
            ->method('getForgotPasswordForm')
            ->willReturn($form);

        $this->passwordRequestHandler->expects($this->once())
            ->method('process')
            ->with($form, $request)
            ->willReturn(null);
        $this->assertFalse($this->forgotPasswordHandler->handle($request));
    }

    public function testHandleProcess()
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->query->add(['isForgotPassword' => true]);

        $form = $this->createMock(FormInterface::class);
        $this->customerUserFormProvider->expects($this->once())
            ->method('getForgotPasswordForm')
            ->willReturn($form);

        $user = $this->createMock(CustomerUser::class);
        $user->expects($this->once())
            ->method('getEmail')
            ->willReturn('test@example.org');
        $this->passwordRequestHandler->expects($this->once())
            ->method('process')
            ->with($form, $request)
            ->willReturn($user);

        $this->session->expects($this->once())
            ->method('set')
            ->with(
                'oro_customer_user_reset_email',
                '...@example.org' // Obfuscated email address
            );

        $this->assertTrue($this->forgotPasswordHandler->handle($request));
        $this->assertNull($request->query->get('isForgotPassword'));
        $this->assertTrue($request->query->get('isCheckEmail'));
    }
}
