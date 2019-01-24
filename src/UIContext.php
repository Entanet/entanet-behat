<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Laracasts\Behat\Context\DatabaseTransactions;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Element\NodeElement;


class UIContext extends MinkContext implements Context
{

    private $javascriptWait = 2500;
    public $page;
    public $session;
    public $row;

    public function __construct()
    {
        $this->page = $this->getPage();
        $this->session = $this->getSession();
    }

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
        $this->session->wait($this->javascriptWait, "document.readyState === 'complete'");
    }

    /**
     * @Then /^the selector :element should have :property
     * @param $element
     * @param $property
     */
    public function theCssSelectorShouldHaveProperty($element, $property)
    {
        $element = $this->page->find('css', $element);
        assertTrue($element->hasClass($property));
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
     */
    public function iClickTableHeader($header)
    {
        $row = $this->page->find('css', sprintf('table th:contains("%s")', $header));
        $this->waitForJavaScript();
        $row->press();
    }

    /**
     * @Given /^The element "(?P<selector>[^"]*)" should have a css property "(?P<property>[^"]*)" with a value of "(?P<value>[^"]*)"$/
     *
     */
    public function assertElementHasCssValue($selector, $property, $value)
    {
        $element = $this->page->find('css', $selector);

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
     *   NodeElement previously selected with $this->getSession()->getPage()->find().
     * @param string $property
     *   Name of the CSS property, such as "visibility".
     * @param string $value
     *   Value of the specified rule, such as "hidden".
     *
     * @return NodeElement|bool
     *   The NodeElement selected if true, FALSE otherwise.
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
     */
    public function clickButtonInRowByText($button, $rowText)
    {
        $this->row = $this->findRowByText($rowText);

        $this->row->pressButton($button);
    }

    /**
     * @Then I OK the JavaScript confirmation
     */
    public function iConfirmTheJsDialogue()
    {
        $selenium = new \Behat\Mink\Driver\Selenium2Driver();
        $selenium->getWebDriverSession()->accept_alert();
    }
}