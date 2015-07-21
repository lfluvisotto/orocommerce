<?php

namespace OroB2B\Bundle\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Oro\Bundle\AddressBundle\Entity\AddressType;

use OroB2B\Bundle\CustomerBundle\Form\DataTransformer\AddressTypeDefaultTransformer;

class CustomerTypedAddressWithDefaultType extends AbstractType
{
    const NAME = 'orob2b_customer_typed_address_with_default';

    /** @var ManagerRegistry */
    protected $registry;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['em'] === null) {
            $em = $this->registry->getManagerForClass($options['class']);
        } else {
            $em = $this->registry->getManager($options['em']);
        }

        $repository = $em->getRepository($options['class']);
        $entities   = $repository->findAll();

        /** @var ClassMetadataInfo $classMetadata */
        $classMetadata   = $em->getClassMetadata($options['class']);
        $identifierField = $classMetadata->getSingleIdentifierFieldName();

        $choiceLabels = [];

        /** @var AddressType $entity */
        foreach ($entities as $entity) {
            $pkValue = $classMetadata->getReflectionProperty($identifierField)->getValue($entity);

            if ($options['property']) {
                $value = $classMetadata->getReflectionProperty($options['property'])->getValue($entity);
            } else {
                $value = (string)$entity;
            }

            $choiceLabels[$pkValue] = $this->translator->trans(
                'orob2b.customer.customer_typed_address_with_default_type.choice.default_text',
                [
                    '%type_name%' => $value
                ]
            );
        }

        $builder->add('default', 'choice', [
            'choices'  => $choiceLabels,
            'multiple' => true,
            'expanded' => true,
            'label'    => false,
        ])
        ->addViewTransformer(new AddressTypeDefaultTransformer($em));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'em'       => null,
            'property' => null
        ]);

        $resolver->setRequired([
            'class'
        ]);
    }

    /**
     * @param ManagerRegistry $registry
     */
    public function setRegistry(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
