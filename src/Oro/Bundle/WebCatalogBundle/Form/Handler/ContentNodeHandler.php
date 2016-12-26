<?php

namespace Oro\Bundle\WebCatalogBundle\Form\Handler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\FormBundle\Event\FormHandler\Events;
use Oro\Bundle\WebCatalogBundle\Generator\SlugGenerator;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\FormBundle\Event\FormHandler\FormProcessEvent;

class ContentNodeHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var SlugGenerator */
    protected $slugGenerator;

    /** @var ObjectManager */
    protected $manager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param Request $request
     * @param SlugGenerator $slugGenerator
     * @param ObjectManager $manager
     * @param EventDispatcherInterface $eventDispatcher
     * @param FormInterface $form
     */
    public function __construct(
        Request $request,
        SlugGenerator $slugGenerator,
        ObjectManager $manager,
        EventDispatcherInterface $eventDispatcher,
        FormInterface $form
    ) {
        $this->request = $request;
        $this->slugGenerator = $slugGenerator;
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        $this->form = $form;
    }

    /**
     * @param ContentNode $contentNode
     *
     * @return bool
     */
    public function process(ContentNode $contentNode)
    {
        $event = new FormProcessEvent($this->form, $contentNode);
        $this->eventDispatcher->dispatch(Events::BEFORE_FORM_DATA_SET, $event);

        if ($event->isFormProcessInterrupted()) {
            return false;
        }

        $this->form->setData($contentNode);

        if (!$this->request->isMethod(Request::METHOD_POST)) {
            return false;
        }

        $event = new FormProcessEvent($this->form, $contentNode);
        $this->eventDispatcher->dispatch(Events::BEFORE_FORM_SUBMIT, $event);

        if ($event->isFormProcessInterrupted()) {
            return false;
        }
        $this->form->submit($this->request);

        if (!$this->form->isValid()) {
            return false;
        }

        $this->onSuccess($contentNode);
        return true;
    }

    /**
     * @param ContentNode $contentNode
     */
    protected function onSuccess(ContentNode $contentNode)
    {
        $this->createDefaultVariantScopes($contentNode);
        $this->slugGenerator->generate($contentNode);
        $this->manager->persist($contentNode);

        $this->eventDispatcher->dispatch(
            Events::BEFORE_FLUSH,
            new AfterFormProcessEvent($this->form, $contentNode)
        );

        $this->manager->flush();

        $this->eventDispatcher->dispatch(
            Events::AFTER_FLUSH,
            new AfterFormProcessEvent($this->form, $contentNode)
        );
    }

    /**
     * @param ContentNode $contentNode
     */
    protected function createDefaultVariantScopes(ContentNode $contentNode)
    {
        $defaultVariant = $contentNode->getDefaultVariant();

        if ($defaultVariant) {
            $defaultVariant->resetScopes();

            $defaultVariantScopes = $this->getDefaultVariantScopes($contentNode);
            foreach ($defaultVariantScopes as $scope) {
                $defaultVariant->addScope($scope);
            }
        }
    }

    /**
     * @param ContentNode $contentNode
     * @return Collection|Scope[]
     */
    protected function getDefaultVariantScopes(ContentNode $contentNode)
    {
        $contentNodeScopes = $contentNode->getScopesConsideringParent();

        $scopes = clone $contentNodeScopes;
        foreach ($contentNode->getContentVariants() as $contentVariant) {
            foreach ($contentVariant->getScopes() as $contentVariantScope) {
                $scopes->removeElement($contentVariantScope);
            }
        }

        return $scopes;
    }
}