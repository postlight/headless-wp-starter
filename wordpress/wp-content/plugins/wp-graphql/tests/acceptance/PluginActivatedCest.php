<?php

class PluginActivatedCest {
	public function seePluginActivated( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnPluginsPage();
		$I->seePluginActivated( 'wp-graphql' );
	}
}
