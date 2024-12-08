<?php
/** @var \App\Elements\Form $form */
echo $form->open();

// About
$settings = $form->fieldset('Settings', 'Plugin\'s main settings.', [
    $form->text('Xperiencify API key'),
]);

// Save
$save = $form->submit( 'Save Changes' );

// Layout
$tabs = \TypeRocket\Elements\Tabs::new()->setFooter( $save )->layoutLeft();
$tabs->tab('Settings', 'admin-generic', $settings)->setDescription('Plugin settings');
$tabs->render();

echo $form->close();