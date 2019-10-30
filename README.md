# JT - Easylink
## Anleitung / Manual
<details>
  <summary>Deutsch/German</summary>

## Deutsche Anleitung
<p>Das Plugin <strong>JT - Easylink</strong> nutzt einen Service von easyrechtssicher.de<br/>Zur Aktivierung des Dienstes registriere Dich bitte <a href="https://easyrechtssicher.de/komplett-schutz/" target="_blank">hier</a></p><p><strong>easyrechtssicher</strong> hat es sich zum Ziel gesetzt, Internetseiten vor Abmahnungen zu schützen.<br/>Dazu werden rechtssichere Impressum, Datenschutzerklärungen, Widerrufsbelehrungen und Allgemeine Geschäftsbedingungen angeboten.</p><p>Anbieter von Internetseiten können sich hier registrieren: <a href="https://easyrechtssicher.de/komplett-schutz/" target="_blank">Komplettschutz</a><br/>Internetagenturen registrieren sich hier: <a href="https://easyrechtssicher.de/mitgliedschaft-webdesigner-agenturen-2/" target="_blank">Agenturangebot</a><br/>Mehr zu diesem Plugin, direkt bei easyrechtssicher: <a href="https://easyrechtssicher.de/plugin_easylink_anleitung/" target="_blank">Anbieterinformation</a></p><p>In den Plugin-Einstellungen muss ein <strong>API-Key</strong> (Zugangsschlüssel) hinterlegt werden, der nach einer Registrierung, über einen der eben genannten Links erhältlich ist.</p><p><strong>Integration</strong><br/>Die Anwendung ist denkbar einfach. Der Plugin-Aufruf ist <code>{jteasylink[ DOKUMENT,SPRACHKÜRZEL]}</code><br/>Werte innerhalb eckiger Klammern <code>[ ]</code> sind optional.</p><p><strong>DOKUMENT</strong> steht als Platzhalter für z.B.:</p><ul><li><strong>dse</strong> - Datenschutzerklärung (Standardwert)</li><li><strong>imp</strong> - Impressum</li><li><strong>wbl</strong> - Widerrufsbelehrung</li></ul><p><strong>SPRACHKÜRZEL</strong> steht als Platzhalter für:</p><ul><li><strong>de</strong> - Deutsch (Standardwert)</li><li><strong>en</strong> - Englisch</li><li>Weitere Länder der EU in Planung</li></ul><p><strong>Beispielaufruf zur Darstellung einer deutschen Datenschutzerklärung:</strong> <code>{jteasylink dse,de}</code></p><p>Die Datenschutzerklärung wird über den Datenschutzgenerator bei easyrechtssicher vorkonfiguriert vom Plugin abgeholt, sodass in Joomla jede weitere Konfiguration entfällt.<br/>Da <code>dse</code> und <code>de</code> die Standardwerte sind, ergibt sich der einfachste Aufruf zu <code>{jteasylink}</code></p><p>Standardmäßig wird automatisch die Sprache verwendet, die für die Ausgabe der Webseite ausgewählt ist.<br/>Sollte es die Sprache nicht geben, wird der SPRACHKÜRZEL ausgewertet.<br/>Fehlt auch diese Sprache, wird der Wert verwendet der in den Plugin-Einstellungen als Standard definiert wurde.</p><p><strong>Es kann auch eine Schnellnavigation angezeigt werden.</strong><br/>Der Plugin-Aufruf ist <code>{jteasylink skiplinks[,DOKUMENT,SPRACHKÜRZEL]}</code><br/>Er muss zusätzlich zur Dokumentenausgabe aufgerufen werden, kann jedoch auch in ein Modul ausgelagert werden.<br/>Auch hier gelten die gleichen Standardwerte für DOKUMENT und SPRACHKÜRZEL und müssen somit für die Ausgabe der Datenschutzerklärung auf Deutsch nicht angegeben werden.</p><p>Um die Schnellnavigation anzuzeigen, bitte in den Einstellungen des Plugins die Option <strong>Ausgabe bearbeiten</strong> einschalten.</p><p>Das war es auch schon.</p><p><strong>Mindestvoraussetzungen</strong></p><ul><li>Joomla! 3.9</li><li>PHP 5.6</li></ul><p><strong>Author:</strong> Guido De Gobbis<br/><strong>Copyright:</strong> © <a href="https://github.com/JoomTools" target="_blank">JoomTools.de</a><br/><strong>Plugin-Lizenz:</strong> <a href="https:/www.gnu.org/licenses/gpl-3.0.de.html" target="_blank">GNU/GPLv3</a><br/><strong>Plugin-Version:</strong> <a href="https://github.com/JoomTools/plg_content_jteasylink/releases/latest">herunterladen</a></p>
</details>

<details>
  <summary>Englisch/English</summary>

## English Manual
<p>The plugin <strong>JT - Easylink</strong> uses a service from easyrechtssicher.de<br/>To activate the service please register <a href="https://easyrechtssicher.de/komplett-schutz/" target="_blank">here</a></p><p><strong>easyrechtssicher</strong> has set itself the goal of protecting websites against warnings.<br/>In addition, legally compliant legal notice, privacy policy and revocation instructions are offered.</p><p>Webpage owners can register here: <a href="https://easyrechtssicher.de/komplett-schutz/" target="_blank">Complete protection</a><br/>Internet agencies register here: <a href="https://easyrechtssicher.de/mitgliedschaft-webdesigner-agenturen-2/" target="_blank">Agency offer</a><br/>More about the plugin, directly at easyrechtssicher: <a href="https://easyrechtssicher.de/plugin_easylink_anleitung/" target="_blank">more information</a></p><p>The plugin settings require an <strong>API-Key</strong> which can be accessed after registration via one of the links above.</p><p><strong>Integration</strong><br/>The usage ist really simple. The plugin call is <code>{jteasylink[ DOCUMENT,LANGUAGE_SHORT_CODE]}</code><br/>values within square brackets <code>[ ]</code> are optional.</p><p><strong>DOKUMENT</strong> is a placeholder for:</p><ul><li><strong>dse</strong> - Privacy Policy (default)</li><li><strong>imp</strong> - Legal Notice</li><li><strong>wbl</strong> - Cancellation policy</a></li></ul><p><strong>LANGUAGE_SHORT_CODE</strong> is a placeholder for:</p><ul><li><strong>de</strong> - German</li><li><strong>en</strong> - English (default)</li><li>Other EU countries in planning</li></ul><p><strong>Example call to display an English privacy policy:</strong> <code>{jteasylink dse,en}</code></p><p>The privacy policy is delivered preconfigured via the privacy policy generator from easyrechtssicher, so no further configuration in Joomla is necessary.</p><p>Because <code>dse</code> and <code>de</code> are the default values, the simplest call on a page in joomla results to <code>{jteasylink}</code></p><p>By default, the language selected for the output of the web page is automatically used.<br/>If the language does not exist, the LANGUAGE_SHORT_CODE is evaluated.<br/>If this language is missing too, the value defined as standard in the plugin settings will be used.</p><p><strong>A quick navigation can also be displayed.</strong><br/>The plugin call is <code>{jteasylink skiplinks[,DOKUMENT,LANGUAGE_SHORT_CODE]}</code><br/>It has to be called in addition to the document output, but can also be moved to a module.<br/>The same default values for DOCUMENT and LANGUAGE_SHORT_CODE also apply here and therefore do not have to be specified for the output of the privacy statement in English.</p><p>To display the quick navigation, please activate the option <strong>Edit output</strong> in the plugin settings.</p><p>That's it.</p><p><strong>Minimum requirements</strong></p><ul><li>Joomla! 3.9</li><li>PHP 5.6</li></ul><p><strong>Author:</strong> Guido De Gobbis<br/><strong>Copyright:</strong> © <a href="https://github.com/JoomTools" target="_blank">JoomTools.de</a><br/><strong>Plugin licens:</strong> <a href="https:/www.gnu.org/licenses/gpl-3.0.en.html" target="_blank">GNU/GPLv3</a><br/><strong>Download</strong> <a href="https://github.com/JoomTools/plg_content_jteasylink/releases/latest">latest Version</a></p>
</details>
