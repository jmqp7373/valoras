<?php
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Valora</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <!-- Incluir el logo de Valora ubicado en assets/images/logo_valoras.png -->
        <img src="../assets/images/logos/logo_valora.png" class='logo' alt="Valoras company logo with stylized lettering on a clean white background conveying a professional and welcoming tone">
        <h2>Crear Cuenta</h2>
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="Numero_de_cedula">Número de Cédula:</label>
                <input type="text" id="Numero_de_cedula" placeholder="Cédula" name="Numero_de_cedula" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">Nombre:</label>
                <input type="text" id="first_name" placeholder="Nombre" name="first_name" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Apellidos:</label>
                <input type="text" id="last_name" placeholder="Apellidos" name="last_name" required>
            </div>
            
            <div class="form-group">
                <label for="phone_number">Número de Celular:</label>
                <div style="display: flex; gap: 8px;">
                    <select id="country_code" name="country_code" style="width: 200px; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; transition: all 0.3s ease;" required>
                        <optgroup label="Países principales">
                            <option value="+57" selected>Colombia (+57)</option>
                            <option value="+58">Venezuela (+58)</option>
                            <option value="+52">México (+52)</option>
                            <option value="+54">Argentina (+54)</option>
                        </optgroup>
                        <optgroup label="Todos los países">
                            <option value="+93">Afganistán (+93)</option>
                            <option value="+355">Albania (+355)</option>
                            <option value="+213">Argelia (+213)</option>
                            <option value="+376">Andorra (+376)</option>
                            <option value="+244">Angola (+244)</option>
                            <option value="+1264">Anguila (+1264)</option>
                            <option value="+1268">Antigua y Barbuda (+1268)</option>
                            <option value="+966">Arabia Saudita (+966)</option>
                            <option value="+374">Armenia (+374)</option>
                            <option value="+297">Aruba (+297)</option>
                            <option value="+61">Australia (+61)</option>
                            <option value="+43">Austria (+43)</option>
                            <option value="+994">Azerbaiyán (+994)</option>
                            <option value="+1242">Bahamas (+1242)</option>
                            <option value="+973">Bahrein (+973)</option>
                            <option value="+880">Bangladesh (+880)</option>
                            <option value="+1246">Barbados (+1246)</option>
                            <option value="+375">Bielorrusia (+375)</option>
                            <option value="+32">Bélgica (+32)</option>
                            <option value="+501">Belice (+501)</option>
                            <option value="+229">Benin (+229)</option>
                            <option value="+1441">Bermudas (+1441)</option>
                            <option value="+975">Bután (+975)</option>
                            <option value="+591">Bolivia (+591)</option>
                            <option value="+387">Bosnia y Herzegovina (+387)</option>
                            <option value="+267">Botsuana (+267)</option>
                            <option value="+55">Brasil (+55)</option>
                            <option value="+673">Brunei (+673)</option>
                            <option value="+359">Bulgaria (+359)</option>
                            <option value="+226">Burkina Faso (+226)</option>
                            <option value="+257">Burundi (+257)</option>
                            <option value="+238">Cabo Verde (+238)</option>
                            <option value="+855">Camboya (+855)</option>
                            <option value="+237">Camerún (+237)</option>
                            <option value="+1">Canadá (+1)</option>
                            <option value="+974">Catar (+974)</option>
                            <option value="+235">Chad (+235)</option>
                            <option value="+56">Chile (+56)</option>
                            <option value="+86">China (+86)</option>
                            <option value="+357">Chipre (+357)</option>
                            <option value="+506">Costa Rica (+506)</option>
                            <option value="+385">Croacia (+385)</option>
                            <option value="+53">Cuba (+53)</option>
                            <option value="+599">Curazao (+599)</option>
                            <option value="+45">Dinamarca (+45)</option>
                            <option value="+253">Djibouti (+253)</option>
                            <option value="+1767">Dominica (+1767)</option>
                            <option value="+593">Ecuador (+593)</option>
                            <option value="+20">Egipto (+20)</option>
                            <option value="+503">El Salvador (+503)</option>
                            <option value="+971">Emiratos Árabes Unidos (+971)</option>
                            <option value="+291">Eritrea (+291)</option>
                            <option value="+421">Eslovaquia (+421)</option>
                            <option value="+386">Eslovenia (+386)</option>
                            <option value="+34">España (+34)</option>
                            <option value="+1">Estados Unidos (+1)</option>
                            <option value="+372">Estonia (+372)</option>
                            <option value="+268">Esuatini (+268)</option>
                            <option value="+251">Etiopía (+251)</option>
                            <option value="+679">Fiyi (+679)</option>
                            <option value="+63">Filipinas (+63)</option>
                            <option value="+358">Finlandia (+358)</option>
                            <option value="+33">Francia (+33)</option>
                            <option value="+241">Gabón (+241)</option>
                            <option value="+220">Gambia (+220)</option>
                            <option value="+995">Georgia (+995)</option>
                            <option value="+233">Ghana (+233)</option>
                            <option value="+350">Gibraltar (+350)</option>
                            <option value="+1473">Granada (+1473)</option>
                            <option value="+30">Grecia (+30)</option>
                            <option value="+299">Groenlandia (+299)</option>
                            <option value="+1671">Guam (+1671)</option>
                            <option value="+502">Guatemala (+502)</option>
                            <option value="+594">Guayana Francesa (+594)</option>
                            <option value="+44">Guernsey (+44)</option>
                            <option value="+224">Guinea (+224)</option>
                            <option value="+245">Guinea-Bisau (+245)</option>
                            <option value="+240">Guinea Ecuatorial (+240)</option>
                            <option value="+592">Guyana (+592)</option>
                            <option value="+509">Haití (+509)</option>
                            <option value="+504">Honduras (+504)</option>
                            <option value="+852">Hong Kong (+852)</option>
                            <option value="+36">Hungría (+36)</option>
                            <option value="+91">India (+91)</option>
                            <option value="+62">Indonesia (+62)</option>
                            <option value="+98">Irán (+98)</option>
                            <option value="+964">Iraq (+964)</option>
                            <option value="+353">Irlanda (+353)</option>
                            <option value="+354">Islandia (+354)</option>
                            <option value="+692">Islas Marshall (+692)</option>
                            <option value="+677">Islas Salomón (+677)</option>
                            <option value="+972">Israel (+972)</option>
                            <option value="+39">Italia (+39)</option>
                            <option value="+1876">Jamaica (+1876)</option>
                            <option value="+81">Japón (+81)</option>
                            <option value="+44">Jersey (+44)</option>
                            <option value="+962">Jordania (+962)</option>
                            <option value="+7">Kazajistán (+7)</option>
                            <option value="+254">Kenia (+254)</option>
                            <option value="+996">Kirguistán (+996)</option>
                            <option value="+686">Kiribati (+686)</option>
                            <option value="+965">Kuwait (+965)</option>
                            <option value="+856">Laos (+856)</option>
                            <option value="+266">Lesoto (+266)</option>
                            <option value="+371">Letonia (+371)</option>
                            <option value="+961">Líbano (+961)</option>
                            <option value="+231">Liberia (+231)</option>
                            <option value="+218">Libia (+218)</option>
                            <option value="+423">Liechtenstein (+423)</option>
                            <option value="+370">Lituania (+370)</option>
                            <option value="+352">Luxemburgo (+352)</option>
                            <option value="+853">Macao (+853)</option>
                            <option value="+389">Macedonia del Norte (+389)</option>
                            <option value="+261">Madagascar (+261)</option>
                            <option value="+60">Malasia (+60)</option>
                            <option value="+265">Malaui (+265)</option>
                            <option value="+960">Maldivas (+960)</option>
                            <option value="+223">Malí (+223)</option>
                            <option value="+356">Malta (+356)</option>
                            <option value="+44">Man, Isla de (+44)</option>
                            <option value="+212">Marruecos (+212)</option>
                            <option value="+596">Martinica (+596)</option>
                            <option value="+230">Mauricio (+230)</option>
                            <option value="+222">Mauritania (+222)</option>
                            <option value="+262">Mayotte (+262)</option>
                            <option value="+691">Micronesia (+691)</option>
                            <option value="+373">Moldavia (+373)</option>
                            <option value="+377">Mónaco (+377)</option>
                            <option value="+976">Mongolia (+976)</option>
                            <option value="+382">Montenegro (+382)</option>
                            <option value="+1664">Montserrat (+1664)</option>
                            <option value="+258">Mozambique (+258)</option>
                            <option value="+95">Myanmar (+95)</option>
                            <option value="+264">Namibia (+264)</option>
                            <option value="+674">Nauru (+674)</option>
                            <option value="+977">Nepal (+977)</option>
                            <option value="+505">Nicaragua (+505)</option>
                            <option value="+227">Níger (+227)</option>
                            <option value="+234">Nigeria (+234)</option>
                            <option value="+683">Niue (+683)</option>
                            <option value="+47">Noruega (+47)</option>
                            <option value="+687">Nueva Caledonia (+687)</option>
                            <option value="+64">Nueva Zelanda (+64)</option>
                            <option value="+968">Omán (+968)</option>
                            <option value="+31">Países Bajos (+31)</option>
                            <option value="+92">Pakistán (+92)</option>
                            <option value="+680">Palaos (+680)</option>
                            <option value="+507">Panamá (+507)</option>
                            <option value="+675">Papúa Nueva Guinea (+675)</option>
                            <option value="+595">Paraguay (+595)</option>
                            <option value="+51">Perú (+51)</option>
                            <option value="+689">Polinesia Francesa (+689)</option>
                            <option value="+48">Polonia (+48)</option>
                            <option value="+351">Portugal (+351)</option>
                            <option value="+1787">Puerto Rico (+1787)</option>
                            <option value="+44">Reino Unido (+44)</option>
                            <option value="+236">República Centroafricana (+236)</option>
                            <option value="+420">República Checa (+420)</option>
                            <option value="+243">República Democrática del Congo (+243)</option>
                            <option value="+1849">República Dominicana (+1849)</option>
                            <option value="+262">Reunión (+262)</option>
                            <option value="+250">Ruanda (+250)</option>
                            <option value="+40">Rumania (+40)</option>
                            <option value="+7">Rusia (+7)</option>
                            <option value="+685">Samoa (+685)</option>
                            <option value="+378">San Marino (+378)</option>
                            <option value="+1869">San Cristóbal y Nieves (+1869)</option>
                            <option value="+1784">San Vicente y las Granadinas (+1784)</option>
                            <option value="+1758">Santa Lucía (+1758)</option>
                            <option value="+239">Santo Tomé y Príncipe (+239)</option>
                            <option value="+221">Senegal (+221)</option>
                            <option value="+381">Serbia (+381)</option>
                            <option value="+248">Seychelles (+248)</option>
                            <option value="+232">Sierra Leona (+232)</option>
                            <option value="+65">Singapur (+65)</option>
                            <option value="+963">Siria (+963)</option>
                            <option value="+252">Somalia (+252)</option>
                            <option value="+94">Sri Lanka (+94)</option>
                            <option value="+27">Sudáfrica (+27)</option>
                            <option value="+249">Sudán (+249)</option>
                            <option value="+211">Sudán del Sur (+211)</option>
                            <option value="+46">Suecia (+46)</option>
                            <option value="+41">Suiza (+41)</option>
                            <option value="+597">Surinam (+597)</option>
                            <option value="+992">Tayikistán (+992)</option>
                            <option value="+66">Tailandia (+66)</option>
                            <option value="+255">Tanzania (+255)</option>
                            <option value="+670">Timor Oriental (+670)</option>
                            <option value="+228">Togo (+228)</option>
                            <option value="+690">Tokelau (+690)</option>
                            <option value="+676">Tonga (+676)</option>
                            <option value="+1868">Trinidad y Tobago (+1868)</option>
                            <option value="+216">Túnez (+216)</option>
                            <option value="+993">Turkmenistán (+993)</option>
                            <option value="+90">Turquía (+90)</option>
                            <option value="+688">Tuvalu (+688)</option>
                            <option value="+380">Ucrania (+380)</option>
                            <option value="+256">Uganda (+256)</option>
                            <option value="+598">Uruguay (+598)</option>
                            <option value="+998">Uzbekistán (+998)</option>
                            <option value="+678">Vanuatu (+678)</option>
                            <option value="+39">Vaticano (+39)</option>
                            <option value="+84">Vietnam (+84)</option>
                            <option value="+967">Yemen (+967)</option>
                            <option value="+260">Zambia (+260)</option>
                            <option value="+263">Zimbabue (+263)</option>
                        </optgroup>
                    </select>
                    <input type="tel" id="phone_number" placeholder="Número de celular" name="phone_number" inputmode="numeric" pattern="\d{6,15}" style="flex: 1; padding: 14px 16px; border: 1px solid #ee6f92; border-radius: 12px; font-size: 16px; font-family: 'Poppins', sans-serif; background-color: #fafafa; transition: all 0.3s ease;" required>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            ¿Ya tienes una cuenta? <a href="login.php" style="color: #882A57; text-decoration: none; font-weight: 500;">Inicia sesión aquí</a>
        </div>
        
    </div>
</body>
</html>