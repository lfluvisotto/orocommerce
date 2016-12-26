<?php

namespace Oro\Bundle\ShippingBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\RuleBundle\Entity\Rule;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodConfig;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestination;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRuleDestinationPostalCode;
use Oro\Bundle\ShippingBundle\Entity\ShippingMethodTypeConfig;
use Oro\Bundle\ShippingBundle\Method\FlatRate\FlatRateShippingMethod;
use Oro\Bundle\ShippingBundle\Method\FlatRate\FlatRateShippingMethodType;
use Symfony\Component\Yaml\Yaml;

class LoadShippingMethodsConfigsRules extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getShippingRuleData() as $reference => $data) {
            $rule = new Rule();
            $rule->setName($reference)
                ->setEnabled($data['rule']['enabled'])
                ->setSortOrder($data['rule']['sortOrder'])
                ->setExpression($data['rule']['expression']);

            $entity = new ShippingMethodsConfigsRule();
            $entity->setRule($rule)
                ->setCurrency($data['currency']);

            if (!array_key_exists('destinations', $data)) {
                $data['destinations'] = [];
            }

            $this->setDestinations($entity, $manager, $data);

            if (array_key_exists('methodConfigs', $data)) {
                foreach ($data['methodConfigs'] as $methodConfigData) {
                    $methodConfig = new ShippingMethodConfig();

                    $methodConfig
                        ->setMethodConfigsRule($entity)
                        ->setMethod(FlatRateShippingMethod::IDENTIFIER);

                    foreach ($methodConfigData['typeConfigs'] as $typeConfigData) {
                        $typeConfig = new ShippingMethodTypeConfig();
                        $typeConfig->setType(FlatRateShippingMethodType::IDENTIFIER)
                            ->setOptions([
                                FlatRateShippingMethodType::PRICE_OPTION => $typeConfigData['options']['price'],
                                FlatRateShippingMethodType::HANDLING_FEE_OPTION => null,
                                FlatRateShippingMethodType::TYPE_OPTION => $typeConfigData['options']['type'],
                            ]);
                        $typeConfig->setEnabled(true);
                        $methodConfig->addTypeConfig($typeConfig);
                    }

                    $manager->persist($methodConfig);
                    $entity->addMethodConfig($methodConfig);
                }
            }

            $manager->persist($entity);

            $this->setReference($reference, $entity);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    protected function getShippingRuleData()
    {
        return Yaml::parse(file_get_contents(__DIR__.'/data/shipping_methods_configs_rules.yml'));
    }

    /**
     * @param ShippingMethodsConfigsRule $entity
     * @param ObjectManager $manager
     * @param array $data
     */
    private function setDestinations(ShippingMethodsConfigsRule $entity, ObjectManager $manager, $data)
    {
        foreach ($data['destinations'] as $destination) {
            /** @var Country $country */
            $country = $manager
                ->getRepository('OroAddressBundle:Country')
                ->findOneBy(['iso2Code' => $destination['country']]);

            $shippingRuleDestination = new ShippingMethodsConfigsRuleDestination();
            $shippingRuleDestination
                ->setMethodConfigsRule($entity)
                ->setCountry($country);

            if (array_key_exists('region', $destination)) {
                /** @var Region $region */
                $region = $manager
                    ->getRepository('OroAddressBundle:Region')
                    ->findOneBy(['combinedCode' => $destination['country'].'-'.$destination['region']]);
                $shippingRuleDestination->setRegion($region);
            }

            if (array_key_exists('postalCodes', $destination)) {
                foreach ($destination['postalCodes'] as $postalCode) {
                    $destinationPostalCode = new ShippingMethodsConfigsRuleDestinationPostalCode();
                    $destinationPostalCode->setName($postalCode['name'])
                        ->setDestination($shippingRuleDestination);

                    $shippingRuleDestination->addPostalCode($destinationPostalCode);
                }
            }

            $manager->persist($shippingRuleDestination);
            $entity->addDestination($shippingRuleDestination);
        }
    }
}