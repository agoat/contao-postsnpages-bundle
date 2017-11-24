<?php
/*
 * Posts'n'pages extension for Contao Open Source CMS.
 *
 * @copyright  Arne Stappen (alias aGoat) 2017
 * @package    contao-postsnpages
 * @author     Arne Stappen <mehh@agoat.xyz>
 * @link       https://agoat.xyz
 * @license    LGPL-3.0
 */

 
$GLOBALS['TL_DCA']['tl_page']['config']['dataContainer'] = 'TableExtended';


$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['icon'] = 'NA';
$GLOBALS['TL_DCA']['tl_page']['list']['sorting']['folders'] = 'root';


// Set new child tables
$GLOBALS['TL_DCA']['tl_page']['config']['ctable'] = array('tl_container', 'tl_archive');


// Replace the generateArticle callback
foreach ($GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'] as &$callback)
{
	if ($callback[0] == 'tl_page' && $callback[1] == 'generateArticle')
	{
		$callback = array('tl_page_extendedarticles', 'generateContainer');
	}
}
unset ($callback);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_page_extendedarticles extends Backend
{

	/**
	 * Automatically create an container in the main column for new pages
	 *
	 * @param DataContainer $dc
	 */
	public function generateContainer(DataContainer $dc)
	{
		// Return if there is no active record (override all)
		if (!$dc->activeRecord)
		{
			return;
		}

		// No title or not a regular page
		if ($dc->activeRecord->title == '' || !in_array($dc->activeRecord->type, array('regular', 'error_403', 'error_404')))
		{
			return;
		}

		/** @var Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $objSessionBag */
		$objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

		$new_records = $objSessionBag->get('new_records');

		// Not a new page
		if (!$new_records || !is_array($new_records[$dc->table]) || !in_array($dc->id, $new_records[$dc->table]))
		{
			return;
		}

		// Check whether there are articles (e.g. on copied pages)
		$objTotal = $this->Database->prepare("SELECT COUNT(*) AS count FROM tl_article WHERE pid=?")
								   ->execute($dc->id);

		if ($objTotal->count > 0)
		{
			return;
		}

		// Create article
		$arrSet['pid'] = $dc->id;
		$arrSet['sorting'] = 128;
		$arrSet['tstamp'] = time();
		$arrSet['inColumn'] = 'main';
		$arrSet['title'] = $dc->activeRecord->title;
		$arrSet['published'] = $dc->activeRecord->published;

		$this->Database->prepare("INSERT INTO tl_container %s")->set($arrSet)->execute();
	}
}
