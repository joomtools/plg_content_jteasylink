# JT - Easylink
## Anleitung / Manual
<details>
  <summary>Deutsch/German</summary>

## Deutsche Anleitung
<p>Das Plugin <strong>JT - Easylink</strong> nutzt einen Service von <a href="https://easyrechtssicher.de" target="_blank">easyrechtssicher</a><br/><strong>easyrechtssicher</strong> hat es sich zum Ziel gesetzt, Internetseiten vor Abmahnungen zu schützen.<br/>Dazu werden rechtssichere Impressum, Datenschutzerklärungen und Allgemeine Geschäftsbedingungen angeboten.</p><p>Anbieter von Internetseiten können sich hier registrieren: <a href="https://easyrechtssicher.de/komplett-schutz/" target="_blank">Komplettschutz</a><br/>Internetagenturen registrieren sich hier: <a href="https://easyrechtssicher.de/mitgliedschaft-webdesigner-agenturen-2/" target="_blank">Agenturangebot</a><br/>Mehr zum Plugin, direkt bei easyrechtssicher: <a href="https://easyrechtssicher.de/plugin_easylink_anleitung/" target="_blank">Anbieterinformation</a></p><p>In den Plugin-Einstellungen muss ein <strong>APIKEY</strong> (Zugangsschlüssel) hinterlegt werden, der nach einer Registrierung, über einen der eben genannten Links, auf der Seite von easyrechtssicher erhältlich ist.</p><p><strong>Integration</strong><br/>Die Anwendung ist denkbar einfach. Der Plugin-Aufruf ist <code>{jteasylink[ DOKUMENT,SPRACHKÜRZEL]}</code></p><p><strong>DOKUMENT</strong> steht als Platzhalter für z.B.:</p><ul><li><strong>dse</strong> - Datenschutzerklärung (Standardwert)</li><li><strong>imp</strong> - Impressum (verfügbar ab 3 Quartal 2019)</li><li><strong>agb</strong> - AGB (allgemeine Geschäftsbedingungen)</li></ul><p><strong>SPRACHKÜRZEL</strong> steht als Platzhalter für:</p><ul><li><strong>de</strong> - Deutsch (Standardwert)</li><li><strong>en</strong> - Englisch</li><li>Weitere Länder der EU in Planung</li></ul><p><strong>Beispielaufruf zur Darstellung einer deutschen Datenschutzerklärung:</strong> <code>{jteasylink dse,de}</code></p><p>Die Datenschutzerklärung wird über den Datenschutzgenerator bei easyrechtssicher vorkonfiguriert vom Plugin abgeholt, sodass in Joomla jede weitere Konfiguration entfällt.<br/>Da <code>dse</code> und <code>de</code> die Standardwerte sind, ergibt sich der einfachste Aufruf zu <code>{jteasylink}</code></p><p>Standardmäßig wird automatisch die Sprache verwendet, die für die Ausgabe der Webseite ausgewählt ist.<br/>Sollte es die Sprache nicht geben, wird der SPRACHKÜRZEL ausgewertet.<br/>Fehlt auch diese Sprache, wird der Wert verwendet der in den Plugin-Einstellungen als Standard definiert wurde.</p><p>Das war es auch schon.</p><p><strong>Mindestvoraussetzungen</strong></p><ul><li>Joomla! 3.9</li><li>PHP 5.6</li></ul><p><strong>Author:</strong> Guido De Gobbis<br/><strong>Copyright:</strong> © <a href="https://github.com/JoomTools" target="_blank">JoomTools.de</a><br/><strong>Plugin-Lizenz:</strong> <a href="https:/www.gnu.org/licenses/gpl-3.0.de.html" target="_blank">GNU/GPLv3</a><br/><strong>Version:</strong> %s</p>
</details>

<details>
  <summary>Englisch/English</summary>

# English Manual

To use this plugin you need a registration on [www.easyrechtssicher.de](https://www.easyrechtssicher.de).  
Not yet registered: [Click here to create an account](https://easyrechtssicher.de/komplett-schutz/)

The application is very easy to use.
The plugin call is {jteasylaw\[ DOCUMENT\]\[,LANGUAGECODE\]}  
The plugin call {jteasylink} automatically returns the default values

DOCUMENT is a placeholder for e.g.:
- _**dse**_ for Privacy-Statement (default)
- _~~**imp** for Imprint~~_
- _~~... for Conditions~~_


LANGUAGECODE is a placeholder for:
- _**de**_ for German
- _**en**_ for English (default)

Specifying a language code is optional.
By default, the language that is selected for the output of the website is automatically used.
If the language is not available, the language set in the plugin will be used.

That's all.

Minimum requirements:
- Joomla! 3.9
- PHP7.1
</details>

