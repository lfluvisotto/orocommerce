<?php

namespace Oro\Bundle\ValidationBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\ValidationBundle\Validator\Constraints\Letters;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\RegexValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class LettersTest extends \PHPUnit_Framework_TestCase
{
    /** @var Letters */
    protected $constraint;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ExecutionContextInterface */
    protected $context;

    /** @var RegexValidator */
    protected $validator;

    protected function setUp()
    {
        $this->constraint = new Letters();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new RegexValidator();
        $this->validator->initialize($this->context);
    }

    public function testConfiguration()
    {
        $this->assertEquals(
            'Symfony\Component\Validator\Constraints\RegexValidator',
            $this->constraint->validatedBy()
        );
        $this->assertEquals(Constraint::PROPERTY_CONSTRAINT, $this->constraint->getTargets());
    }

    public function testGetAlias()
    {
        $this->assertEquals('letters', $this->constraint->getAlias());
    }

    public function testGetDefaultOption()
    {
        $this->assertEquals(null, $this->constraint->getDefaultOption());
    }

    /**
     * @dataProvider validateDataProvider
     * @param mixed $data
     * @param boolean $correct
     */
    public function testValidate($data, $correct)
    {
        if (!$correct) {
            $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
            $this->context->expects($this->once())
                ->method('buildViolation')
                ->with($this->constraint->message)
                ->willReturn($builder);
            $builder->expects($this->once())
                ->method('setParameter')
                ->willReturnSelf();
            $builder->expects($this->once())
                ->method('setCode')
                ->willReturnSelf();
            $builder->expects($this->once())
                ->method('addViolation');
        } else {
            $this->context->expects($this->never())
                ->method('buildViolation');
        }

        $this->validator->validate($data, $this->constraint);
    }

    public function validateDataProvider()
    {
        return [
            'correct' => [
                'data' => 'AbcAbc',
                'correct' => true
            ],
            'not correct' => [
                'data' => 'Abc Abc',
                'correct' => false
            ]
        ];
    }
}
