<?php
$page_title = "CTF - TechShop Security Lab";
$current_page = 'ctf';
$show_security_warning = false;

require_once 'config.php';

// Alle geldige vlaggen met bijbehorende uitdaging
$geldige_vlaggen = [
    'FLAG{sql_login_bypass_gelukt}'    => ['nr' => 1, 'naam' => 'SQL Injectie — Login Bypass'],
    'FLAG{union_select_geheim_gevonden}' => ['nr' => 2, 'naam' => 'SQL Injectie — UNION Attack'],
    'FLAG{admin_zonder_login}'          => ['nr' => 3, 'naam' => 'Broken Access Control'],
    'FLAG{plaintext_is_nooit_veilig}'   => ['nr' => 4, 'naam' => 'Sensitive Data Exposure'],
    'FLAG{stored_xss_cookie_gestolen}'  => ['nr' => 5, 'naam' => 'Stored XSS'],
    'FLAG{reflected_xss_in_broncode}'   => ['nr' => 6, 'naam' => 'Reflected XSS'],
    'FLAG{idor_geheime_data_bereikt}'   => ['nr' => 7, 'naam' => 'IDOR — Verborgen Data'],
    'FLAG{input_validatie_omzeild}'     => ['nr' => 8, 'naam' => 'Input Validatie Failure'],
];

$submit_bericht = '';
$submit_fout    = '';

// Vlag inleveren
if ($_POST && isset($_POST['naam'], $_POST['vlag'])) {
    $naam = trim($_POST['naam']);
    $vlag = trim($_POST['vlag']);

    if (!$naam) {
        $submit_fout = "Vul je naam in.";
    } elseif (!$vlag) {
        $submit_fout = "Vul een vlag in.";
    } elseif (!isset($geldige_vlaggen[$vlag])) {
        $submit_fout = "Onbekende of onjuiste vlag. Probeer het opnieuw!";
    } else {
        $challenge = $geldige_vlaggen[$vlag];

        // Controleer of deze naam+vlag al eerder is ingediend
        $check = executeQuery("SELECT id FROM ctf_submissions WHERE naam = '$naam' AND vlag = '$vlag'");
        if ($check->num_rows > 0) {
            $submit_fout = "Je hebt vlag #{$challenge['nr']} al eerder ingeleverd!";
        } else {
            $challenge_naam = $challenge['naam'];
            executeQuery("INSERT INTO ctf_submissions (naam, vlag, challenge_naam) VALUES ('$naam', '$vlag', '$challenge_naam')");
            $submit_bericht = "Vlag #{$challenge['nr']} correct! <strong>{$challenge['naam']}</strong> gevonden!";
            logAction("CTF vlag ingediend: $vlag door $naam");
        }
    }
}

// Scorebord ophalen
$scores_result = executeQuery("
    SELECT naam, COUNT(*) as aantal, GROUP_CONCAT(challenge_naam, ' | ') as gevonden
    FROM ctf_submissions
    GROUP BY naam
    ORDER BY aantal DESC, MIN(ingediend_op) ASC
");

// Recente inzendingen
$recent_result = executeQuery("
    SELECT naam, challenge_naam, ingediend_op
    FROM ctf_submissions
    ORDER BY ingediend_op DESC
    LIMIT 20
");

include 'includes/header.php';
?>

<style>
.ctf-hero {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
    color: white;
    padding: 3rem 0;
    text-align: center;
}
.ctf-hero h2 { font-size: 2.5rem; margin-bottom: 0.5rem; }
.ctf-hero p  { font-size: 1.1rem; opacity: 0.8; }

.challenges-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.challenge-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border-left: 5px solid #667eea;
    position: relative;
}
.challenge-card.gevaarlijk { border-left-color: #dc3545; }
.challenge-card.gemiddeld  { border-left-color: #fd7e14; }
.challenge-card.beginner   { border-left-color: #28a745; }

.challenge-nr {
    position: absolute;
    top: 1rem; right: 1rem;
    background: #667eea;
    color: white;
    border-radius: 50%;
    width: 2rem; height: 2rem;
    display: flex; align-items: center; justify-content: center;
    font-weight: bold; font-size: 0.9rem;
}
.gevaarlijk .challenge-nr { background: #dc3545; }
.gemiddeld  .challenge-nr { background: #fd7e14; }
.beginner   .challenge-nr { background: #28a745; }

.challenge-title { font-size: 1.1rem; font-weight: bold; margin-bottom: 0.5rem; color: #333; }
.challenge-category { font-size: 0.8rem; color: #888; margin-bottom: 0.75rem; }
.challenge-hint {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 0.75rem;
    font-size: 0.85rem;
    color: #555;
    margin-top: 0.75rem;
    border-left: 3px solid #dee2e6;
}
.challenge-hint code {
    background: #e9ecef;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
    font-family: monospace;
    font-size: 0.8rem;
}
.niveau-badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: bold;
    margin-bottom: 0.75rem;
}
.badge-beginner   { background: #d4edda; color: #155724; }
.badge-gemiddeld  { background: #fff3cd; color: #856404; }
.badge-gevaarlijk { background: #f8d7da; color: #721c24; }

.score-table { width: 100%; border-collapse: collapse; }
.score-table th, .score-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #dee2e6; }
.score-table th { background: #f8f9fa; font-weight: bold; }
.score-table tr:hover { background: #f8f9fa; }
.rank-1 td { background: #fff9e6; font-weight: bold; }
.rank-2 td { background: #f8f9fa; }

.flag-input {
    font-family: monospace;
    letter-spacing: 1px;
    background: #1a1a2e;
    color: #00ff41;
    border: 2px solid #0f3460;
}
.flag-input:focus { border-color: #667eea; outline: none; }

.submit-box {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border-radius: 12px;
    padding: 2rem;
    color: white;
}
.submit-box label { color: #aaa; }
.submit-box input { color: white; }
</style>

<section class="ctf-hero">
    <div class="container">
        <h2>🚩 TechShop Security Lab — CTF</h2>
        <p>Vind alle 8 verborgen vlaggen door de beveiligingslekken te exploiteren!</p>
        <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.6;">
            Vlaggen hebben het formaat: <code style="background: rgba(255,255,255,0.1); padding: 0.2rem 0.5rem; border-radius: 4px;">FLAG{...}</code>
        </p>
    </div>
</section>

<section style="padding: 3rem 0; background: #f8f9fa;">
    <div class="container">

        <!-- Uitdagingen -->
        <h3 style="margin-bottom: 0.5rem;"><i class="fas fa-crosshairs"></i> De 8 Uitdagingen</h3>
        <p style="color: #666; margin-bottom: 1rem;">Elke uitdaging verwijst naar een beveiligingslek in de webshop. Exploiteer het lek om de vlag te vinden.</p>

        <div class="challenges-grid">

            <!-- Uitdaging 1 -->
            <div class="challenge-card beginner">
                <div class="challenge-nr">1</div>
                <div class="niveau-badge badge-beginner">Beginner</div>
                <div class="challenge-title"><i class="fas fa-key"></i> SQL Injectie — Login Bypass</div>
                <div class="challenge-category">OWASP A03 · login.php</div>
                <p style="font-size:0.9rem; color:#555;">Log in als de geheime gebruiker <strong>geheim</strong> zonder zijn wachtwoord te kennen. De vlag verschijnt als je succesvol bent ingelogd.</p>
                <div class="challenge-hint">
                    💡 Hint: SQL injectie in het gebruikersnaamveld.<br>
                    Probeer: <code>geheim' --</code>
                </div>
            </div>

            <!-- Uitdaging 2 -->
            <div class="challenge-card gemiddeld">
                <div class="challenge-nr">2</div>
                <div class="niveau-badge badge-gemiddeld">Gemiddeld</div>
                <div class="challenge-title"><i class="fas fa-database"></i> SQL Injectie — UNION Attack</div>
                <div class="challenge-category">OWASP A03 · products.php</div>
                <p style="font-size:0.9rem; color:#555;">Er bestaat een geheime tabel genaamd <code>geheimen</code> in de database. Gebruik een UNION SELECT aanval via de zoekbalk om de inhoud op te halen.</p>
                <div class="challenge-hint">
                    💡 Hint: De <code>products</code> tabel heeft 7 kolommen.<br>
                    Probeer in de zoekbalk: <code>%' UNION SELECT ...</code>
                </div>
            </div>

            <!-- Uitdaging 3 -->
            <div class="challenge-card beginner">
                <div class="challenge-nr">3</div>
                <div class="niveau-badge badge-beginner">Beginner</div>
                <div class="challenge-title"><i class="fas fa-door-open"></i> Broken Access Control</div>
                <div class="challenge-category">OWASP A01 · admin.php</div>
                <p style="font-size:0.9rem; color:#555;">Het admin-paneel heeft geen toegangscontrole. Bezoek het zonder ingelogd te zijn. De vlag staat er gewoon op.</p>
                <div class="challenge-hint">
                    💡 Hint: Log uit en ga rechtstreeks naar <code>/admin.php</code>
                </div>
            </div>

            <!-- Uitdaging 4 -->
            <div class="challenge-card beginner">
                <div class="challenge-nr">4</div>
                <div class="niveau-badge badge-beginner">Beginner</div>
                <div class="challenge-title"><i class="fas fa-eye"></i> Sensitive Data Exposure</div>
                <div class="challenge-category">OWASP A02 · admin.php</div>
                <p style="font-size:0.9rem; color:#555;">Wachtwoorden worden in plaintext opgeslagen. Er is een gebruiker wiens wachtwoord zelf de vlag is. Zoek hem op in het admin-paneel.</p>
                <div class="challenge-hint">
                    💡 Hint: Log in als <code>admin</code> / <code>admin123</code> en bekijk de gebruikerstabel in het admin-paneel.
                </div>
            </div>

            <!-- Uitdaging 5 -->
            <div class="challenge-card gemiddeld">
                <div class="challenge-nr">5</div>
                <div class="niveau-badge badge-gemiddeld">Gemiddeld</div>
                <div class="challenge-title"><i class="fas fa-code"></i> Stored XSS — Cookie Stelen</div>
                <div class="challenge-category">OWASP A03 · product.php</div>
                <p style="font-size:0.9rem; color:#555;">Op elke productpagina staat een CTF-cookie. Gebruik een Stored XSS-payload in een review om de cookie-waarde zichtbaar te maken.</p>
                <div class="challenge-hint">
                    💡 Hint: Voeg een review toe met:<br>
                    <code>&lt;script&gt;alert(document.cookie)&lt;/script&gt;</code><br>
                    De vlag staat in de cookie-string.
                </div>
            </div>

            <!-- Uitdaging 6 -->
            <div class="challenge-card gemiddeld">
                <div class="challenge-nr">6</div>
                <div class="niveau-badge badge-gemiddeld">Gemiddeld</div>
                <div class="challenge-title"><i class="fas fa-link"></i> Reflected XSS — Broncode</div>
                <div class="challenge-category">OWASP A03 · products.php</div>
                <p style="font-size:0.9rem; color:#555;">Er zit een vlag verborgen in de HTML-broncode van de zoekpagina. Bekijk de paginabron nadat je een zoekopdracht hebt gedaan.</p>
                <div class="challenge-hint">
                    💡 Hint: Doe een zoekopdracht en druk op <code>Ctrl+U</code> om de broncode te bekijken. Zoek naar <code>CTF</code>.
                </div>
            </div>

            <!-- Uitdaging 7 -->
            <div class="challenge-card gemiddeld">
                <div class="challenge-nr">7</div>
                <div class="niveau-badge badge-gemiddeld">Gemiddeld</div>
                <div class="challenge-title"><i class="fas fa-user-secret"></i> IDOR — Verborgen Product</div>
                <div class="challenge-category">OWASP A01 · product.php</div>
                <p style="font-size:0.9rem; color:#555;">Er bestaat een geheim product dat niet in de productenlijst staat. Manipuleer het product-ID in de URL om het te vinden.</p>
                <div class="challenge-hint">
                    💡 Hint: Producten zijn bereikbaar via <code>/product.php?id=X</code>. Probeer ID's buiten de normale lijst.
                </div>
            </div>

            <!-- Uitdaging 8 -->
            <div class="challenge-card beginner">
                <div class="challenge-nr">8</div>
                <div class="niveau-badge badge-beginner">Beginner</div>
                <div class="challenge-title"><i class="fas fa-filter"></i> Input Validatie Failure</div>
                <div class="challenge-category">OWASP A04 · register.php</div>
                <p style="font-size:0.9rem; color:#555;">Het registratieformulier valideert e-mailadressen niet. Registreer met een ongeldig e-mailadres om de vlag te onthullen.</p>
                <div class="challenge-hint">
                    💡 Hint: Gebruik als e-mailadres: <code>@</code> of <code>geen-email</code>
                </div>
            </div>

        </div><!-- /.challenges-grid -->

        <!-- Vlag inleveren -->
        <div class="submit-box" style="margin-top: 3rem;">
            <h3 style="color: #00ff41; margin-bottom: 1.5rem;"><i class="fas fa-flag"></i> Vlag Inleveren</h3>

            <?php if ($submit_bericht): ?>
                <div style="background: #00ff41; color: #1a1a2e; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: bold;">
                    ✅ <?php echo $submit_bericht; ?>
                </div>
            <?php endif; ?>

            <?php if ($submit_fout): ?>
                <div style="background: #dc3545; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    ❌ <?php echo $submit_fout; ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 1rem; align-items: end;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Jouw naam / team</label>
                    <input type="text" name="naam" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['naam'] ?? ''); ?>"
                           placeholder="Naam..." required
                           style="background: rgba(255,255,255,0.1); border: 1px solid #0f3460; color: white;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #aaa;">Gevonden vlag</label>
                    <input type="text" name="vlag" class="form-control flag-input"
                           value="<?php echo htmlspecialchars($_POST['vlag'] ?? ''); ?>"
                           placeholder="FLAG{...}" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-success" style="width: 100%; padding: 0.75rem 1.5rem;">
                        <i class="fas fa-paper-plane"></i> Inleveren
                    </button>
                </div>
            </form>
        </div>

        <!-- Scorebord -->
        <div style="background: white; border-radius: 12px; padding: 2rem; margin-top: 2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h3><i class="fas fa-trophy"></i> Scorebord</h3>

            <?php if ($scores_result->num_rows === 0): ?>
                <p style="text-align: center; color: #888; padding: 2rem;">
                    Nog niemand heeft een vlag ingediend. Wees de eerste!
                </p>
            <?php else: ?>
                <table class="score-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Naam / Team</th>
                            <th>Vlaggen</th>
                            <th>Gevonden uitdagingen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $rank = 1; while ($row = $scores_result->fetch_assoc()): ?>
                            <tr class="rank-<?php echo $rank; ?>">
                                <td>
                                    <?php if ($rank === 1): ?>🥇
                                    <?php elseif ($rank === 2): ?>🥈
                                    <?php elseif ($rank === 3): ?>🥉
                                    <?php else: echo $rank; ?>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['naam']); ?></strong></td>
                                <td>
                                    <span style="background: #667eea; color: white; padding: 0.2rem 0.7rem; border-radius: 20px; font-weight: bold;">
                                        <?php echo $row['aantal']; ?> / 8
                                    </span>
                                    <?php if ($row['aantal'] == 8): ?> 🏆<?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem; color: #666;">
                                    <?php echo htmlspecialchars($row['gevonden']); ?>
                                </td>
                            </tr>
                        <?php $rank++; endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Recente inzendingen -->
        <?php if ($recent_result->num_rows > 0): ?>
        <div style="background: white; border-radius: 12px; padding: 2rem; margin-top: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
            <h4><i class="fas fa-history"></i> Recente Inzendingen</h4>
            <table class="score-table" style="margin-top: 1rem;">
                <thead>
                    <tr><th>Naam</th><th>Uitdaging</th><th>Tijdstip</th></tr>
                </thead>
                <tbody>
                    <?php while ($r = $recent_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['naam']); ?></td>
                            <td><?php echo htmlspecialchars($r['challenge_naam']); ?></td>
                            <td style="color: #888; font-size: 0.85rem;">
                                <?php echo date('d-m H:i', strtotime($r['ingediend_op'])); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>
