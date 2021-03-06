<?php

namespace Oro\Bundle\WebsiteBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\FrontendLocalizationBundle\Layout\DataProvider\FrontendLocalizationProvider;
use Oro\Bundle\FrontendLocalizationBundle\Manager\UserLocalizationManager;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\TranslationBundle\Entity\Language;
use Oro\Component\Layout\ContextInterface;

class FrontendLocalizationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserLocalizationManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $userLocalizationManager;

    /**
     * @var FrontendLocalizationProvider
     */
    protected $dataProvider;

    protected function setUp()
    {
        $this->userLocalizationManager = $this->getMockBuilder(UserLocalizationManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new FrontendLocalizationProvider($this->userLocalizationManager);
    }

    public function testGetEnabledLocalization()
    {
        $localizations = [new Localization(), new Localization()];

        $this->userLocalizationManager->expects($this->once())
            ->method('getEnabledLocalizations')
            ->willReturn($localizations);

        $this->assertSame($localizations, $this->dataProvider->getEnabledLocalizations());
    }

    public function testGetCurrentLocalization()
    {
        $localization = new Localization();

        $this->userLocalizationManager->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->assertSame($localization, $this->dataProvider->getCurrentLocalization());
    }

    public function testGetCurrentLanguageCode()
    {
        $languageCode = 'de_DE';
        $expectedLanguageCode = 'de-DE';

        $localization = $this->getLocalizationWithLanguage($languageCode);

        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn($localization);

        $this->assertSame($expectedLanguageCode, $this->dataProvider->getCurrentLanguageCode());
    }

    public function testGetCurrentLanguageCodeWhenNoCurrent()
    {
        $languageCode = 'en_US';
        $expectedLanguageCode = 'en-US';

        $localization = $this->getLocalizationWithLanguage($languageCode);

        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getCurrentLocalization')
            ->willReturn(null);

        $this->userLocalizationManager
            ->expects($this->once())
            ->method('getDefaultLocalization')
            ->willReturn($localization);

        $this->assertSame($expectedLanguageCode, $this->dataProvider->getCurrentLanguageCode());
    }

    /**
     * @param string $languageCode
     *
     * @return Localization
     */
    private function getLocalizationWithLanguage(string $languageCode): Localization
    {
        $language = new Language();
        $language->setCode($languageCode);

        $localization = new Localization();
        $localization->setLanguage($language);

        return $localization;
    }
}
