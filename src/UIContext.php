<?php


namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Laracasts\Behat\Context\DatabaseTransactions;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Element\NodeElement;
use Exception;

class UIContext extends MinkContext implements Context
{

    private $javascriptWait = 2500;
    public $page;
    public $session;
    public $row;


    /**
     * @BeforeScenario
     */
    public function setUp()
    {

    }


    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }

    /**
     * @return \Behat\Mink\Element\DocumentElement
     */
    private function getPage()
    {
        return $this->getSession()->getPage();
    }


    public function waitForJavaScript()
    {
        $this->getSession()->wait($this->javascriptWait, "document.readyState === 'complete'");
    }

    /**
     * @Then I wait for :secs seconds
     * @param $secs
     */
    public function waitForSeconds($secs)
    {
        $secs = $secs * 1000;
        $this->getSession()->wait($secs);
    }

    /**
     * @Then /^the selector :element should have :property
     * @param $element
     * @param $property
     */
    public function theCssSelectorShouldHaveProperty($element, $property)
    {
        $element = $this->getPage()->find('css', $element);
        assertTrue($element->hasClass($property));
    }

    /**
     * @When I click :linkName
     * @param $linkName
     * @throws
     */
    public function iClick($linkName)
    {
        $this->getPage()->clickLink($linkName);
    }

    /**
     * Pauses the scenario until the user presses a key. Useful when debugging a scenario.
     *
     * @Then /^break$/
     */
    public function iPutABreakpoint()
    {
        fwrite(STDOUT, "\033[s    \033[93m[Breakpoint] Press \033[1;93m[RETURN]\033[0;93m to continue...\033[0m");
        while (fgets(STDIN, 1024) == '') {
        }
        fwrite(STDOUT, "\033[u");

        return;
    }

    /**
     * @Then I click table header :header
     * @param $header
     */
    public function iClickTableHeader($header)
    {
        $row = $this->getPage()->find('css', sprintf('table th:contains("%s")', $header));
        $this->waitForJavaScript();
        $row->press();
    }

    /**
     * @Given /^The element "(?P<selector>[^"]*)" should have a css property "(?P<property>[^"]*)" with a value of "(?P<value>[^"]*)"$/
     * @param $selector
     * @param $property
     * @param $value
     * @throws
     */
    public function assertElementHasCssValue($selector, $property, $value)
    {
        $element = $this->getPage()->find('css', $selector);

        if (empty($element)) {
            $message = sprintf('Could not find element using the selector "%s"', $selector);
            throw new \Exception($message);
        }
        $style = $this->elementHasCSSValue($element, $property, $value);
        if (empty($style)) {
            $message = sprintf('The property "%s" for the selector "%s" is not "%s"', $property, $selector, $value);
            throw new \Exception($message);
        }
    }

    /**
     * Determine if a Mink NodeElement contains a specific css rule attribute value.
     *
     * @param NodeElement $element
     * @param string $property
     *
     * @param string $value
     *
     * @return bool
     *
     */
    protected function elementHasCSSValue($element, $property, $value)
    {
        $exists = FALSE;
        $style = $element->getAttribute('style');
        if ($style) {
            if (preg_match("/(^{$property}:|; {$property}:) ([a-z0-9]+);/i", $style, $matches)) {
                $found = array_pop($matches);
                if ($found == $value) {
                    $exists = $element;
                }
            }
        }
        return $exists;
    }

    /**
     * @param $rowText
     * @return \Behat\Mink\Element\NodeElement
     */
    private function findRowByText($rowText)
    {
        $this->row = $this->getPage()->find('css', sprintf('table tr:contains("%s")', $rowText));
        assertNotNull($this->row, 'Cannot find a table row with this text!');

        return $this->row;
    }

    /**
     * @Then I click the :button on the row that contains :rowText
     * @throws
     */
    public function clickButtonInRowByText($button, $rowText)
    {
        $this->row = $this->findRowByText($rowText);

        try {
            $this->row->pressButton($button);
        } catch
        (Exception $e) {
            throw new Exception("Could not find button $button.");
        }
    }


    /**
     * @Then I assert the row that contains :rowText has class :css
     * @param $rowText
     */
    public function assertCssInsideRow($rowText, $css)
    {
        $this->row = $this->findRowByText($rowText);

        assertContains($css, $this->row);
    }

    /**
     * Saving a screen shot
     * @When I save a screen shot to :filename
     * @param $filename
     */
    public function iSaveAScreenshotIn($filename)
    {
        sleep(1);
        $this->saveScreenshot($filename, __DIR__ . '/../..');
    }

    /**
     * @Then I OK the JavaScript confirmation
     */
    public function iConfirmTheJsDialogue()
    {
        $selenium = new \Behat\Mink\Driver\Selenium2Driver();
        $selenium->getWebDriverSession()->accept_alert();
    }

    /**
     * @Then I expect CSS locator :locator has text :text
     * @param $locator
     * @param $text
     * @return bool
     */
    public function getText($locator, $text)
    {
        $getText = $this->getSession()->getPage()->find('css', $locator)->getText();

        if ($getText === $text) {
            return true;
        }
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" in popup$/
     *
     * @param $message
     *
     * @return bool
     */
    public function assertPopupMessageText($message)
    {
        return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
    }

    /**
     * @Then I fill in the pop up with :text
     * @param $text
     */
    public function fillInReason($text)
    {
        $this->getSession()->getDriver()->getWebDriverSession()->postAlert_text(array("text" => $text));
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
        $this->waitForJavaScript();
    }


    /**
     * @Then I assert the :element has class :class
     * @param $selector
     * @param $class
     */
    public function assertElementHasClass($selector, $class)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('named', array('id', $selector));
        assertTrue(!$element->hasClass($class));
    }

    /**
     * @Then I ensure the :toggle is checked and has text :text
     * @param $toggle
     * @param $text
     */
    public function theCheckBoxIsChecked($toggle, $text)
    {
        $switch = $this->getText($toggle, $text);

        if ($switch == false) {
            $this->getPage()->find('css', $toggle)->press();
        }
    }


    /**
     * @Then I visit the login page
     */
    public function iVisitTheLoginPage()
    {
        $this->visit('/login');
    }

    /**
     * @Then I visit the register page
     */
    public function iVisitTheRegisterPage()
    {
        $this->visit('/register');
    }

    /**
     * @When I click the :selector element
     * @param $selector
     * @throws
     */
    public function iClickTheElement($selector)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $selector);

        if (empty($element)) {
            throw new Exception("No html element found for the selector ('$selector')");
        }

        $element->click();
        $this->javascriptWait;
    }

    /**
     * @Given I scroll the window to :x :y
     * @throws
     */
    public function iScrollToXY($x, $y)
    {
        try {
            $this->getSession()->executeScript("(function(){window.scrollTo($x, $y);})();");
        } catch (Exception $e) {
            throw new \Exception("ScrollIntoView failed");
        }
    }


    /**
     *@Given I fill in :field with a unique string 
     *@param $field 
     * str_replace is used to remove special characters
     */
    public function iFillFieldWithAUniqueString($field)
    {
        $uniqueString = substr(md5(rand()), 0, 7);
        $carbonDate = Carbon::now()->toTimeString();
        $formatString = str_replace( array( '\'', '"', ',' , ';', '<', '>', '-', ':' ), '', $carbonDate.$uniqueString);
        $this->fillField($field, $formatString);
    }


    /**
     *@When I fill in :field with :text
     *@param $field, $text
     */
    public function iFillFieldWithText($field, $text)
    {
        $this->fillField($field, $text);
    }




    /**
     * UNIFY TEMPLATE RELATED FUNCTIONS
     */


    /**
     * @Then I go to the :link via the Menu
     * @param $link
     * @throws
     */
    public function iGoToLinkViaMenu($link)
    {
        try {
            $this->iClickTheElement('#menu');
        } catch (Exception $e) {
            throw new Exception('Menu not found. Page loader was likely present at the time.');
        }
        $this->clickLink($link);
        $this->javascriptWait;
    }


}