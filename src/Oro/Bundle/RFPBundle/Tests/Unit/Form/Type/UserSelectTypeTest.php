<?php

namespace Oro\Bundle\RFPBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\RFPBundle\Form\Type\UserSelectType;
use Oro\Bundle\UserBundle\Form\Type\UserSelectType as BaseUserSelectType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserSelectType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry $registry */
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formType = new UserSelectType($registry);
    }

    /**
     * Test getParent
     */
    public function testGetParent()
    {
        $this->assertEquals(BaseUserSelectType::class, $this->formType->getParent());
    }

    /**
     * Test configureOptions
     */
    public function testConfigureOptions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $resolver->expects($this->once())
            ->method('setDefaults');

        $this->formType->configureOptions($resolver);
    }
}
