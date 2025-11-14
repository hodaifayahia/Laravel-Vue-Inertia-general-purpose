<?php

return [
    // Main settings
    'title' => 'Nustatymai',
    'description' => 'Tvarkykite savo profilį ir paskyros nustatymus',
    
    // Navigation
    'nav' => [
        'profile' => 'Profilis',
        'password' => 'Slaptažodis',
        'two_factor' => 'Dviejų faktorių autentifikacija',
        'appearance' => 'Išvaizda',
        'customization' => 'Pritaikymas',
    ],
    
    // Profile settings
    'profile' => [
        'title' => 'Profilio nustatymai',
        'heading' => 'Profilio informacija',
        'description' => 'Atnaujinkite savo vardą ir el. pašto adresą',
        'name' => 'Vardas',
        'email' => 'El. pašto adresas',
        'name_placeholder' => 'Pilnas vardas',
        'email_placeholder' => 'El. pašto adresas',
        'save' => 'Išsaugoti',
        'saved' => 'Išsaugota.',
        'email_unverified' => 'Jūsų el. pašto adresas nepatvirtintas.',
        'resend_verification' => 'Spustelėkite čia, kad iš naujo išsiųstumėte patvirtinimo el. laišką.',
        'verification_sent' => 'Nauja patvirtinimo nuoroda buvo išsiųsta į jūsų el. pašto adresą.',
    ],
    
    // Password settings
    'password' => [
        'title' => 'Slaptažodžio nustatymai',
        'heading' => 'Atnaujinti slaptažodį',
        'description' => 'Įsitikinkite, kad jūsų paskyra naudoja ilgą, atsitiktinį slaptažodį, kad išliktų saugi',
        'current_password' => 'Dabartinis slaptažodis',
        'new_password' => 'Naujas slaptažodis',
        'confirm_password' => 'Patvirtinti slaptažodį',
        'current_password_placeholder' => 'Dabartinis slaptažodis',
        'new_password_placeholder' => 'Naujas slaptažodis',
        'confirm_password_placeholder' => 'Patvirtinti slaptažodį',
        'save' => 'Išsaugoti slaptažodį',
        'saved' => 'Išsaugota.',
    ],
    
    // Appearance settings
    'appearance' => [
        'title' => 'Išvaizdos nustatymai',
        'heading' => 'Išvaizdos nustatymai',
        'description' => 'Atnaujinkite savo paskyros išvaizdos nustatymus',
        'language' => 'Kalba',
        'language_description' => 'Pasirinkite pageidaujamą kalbą',
        'save' => 'Išsaugoti nuostatas',
        'saved' => 'Išsaugota.',
    ],
    
    // Two-Factor Authentication
    'two_factor' => [
        'title' => 'Dviejų faktorių autentifikacija',
        'heading' => 'Dviejų faktorių autentifikacija',
        'description' => 'Tvarkykite dviejų faktorių autentifikacijos nustatymus',
        'enabled' => 'Įjungta',
        'disabled' => 'Išjungta',
        'enable' => 'Įjungti 2FA',
        'disable' => 'Išjungti 2FA',
        'continue_setup' => 'Tęsti sąranką',
        'enabled_description' => 'Įjungus dviejų faktorių autentifikaciją, prisijungimo metu būsite paraginti įvesti saugų, atsitiktinį kodą, kurį galite gauti iš TOTP palaikančios programos savo telefone.',
        'disabled_description' => 'Kai įjungsite dviejų faktorių autentifikaciją, prisijungimo metu būsite paraginti įvesti saugų kodą. Šį kodą galima gauti iš TOTP palaikančios programos savo telefone.',
        'setup_title' => 'Įjungti dviejų faktorių autentifikaciją',
        'setup_description' => 'Dviejų faktorių autentifikacija prideda papildomą saugumo sluoksnį prie jūsų paskyros, reikalaudama daugiau nei tik slaptažodžio prisijungimui.',
        'scan_qr' => 'Nuskaitykite QR kodą žemiau su savo autentifikavimo programa',
        'manual_entry' => 'Arba įveskite šį kodą rankiniu būdu:',
        'enter_code' => 'Įveskite 6 skaitmenų kodą iš savo autentifikavimo programos',
        'code_placeholder' => '000000',
        'verify' => 'Patikrinti ir įjungti',
        'recovery_codes' => 'Atkūrimo kodai',
        'recovery_codes_description' => 'Išsaugokite šiuos atkūrimo kodus saugiame slaptažodžių tvarkytuve. Juos galima naudoti norint atkurti prieigą prie jūsų paskyros, jei prarasite dviejų faktorių autentifikacijos įrenginį.',
        'regenerate_codes' => 'Regeneruoti atkūrimo kodus',
        'show_codes' => 'Rodyti atkūrimo kodus',
        'hide_codes' => 'Slėpti atkūrimo kodus',
        'download_codes' => 'Atsisiųsti',
        'copy_codes' => 'Kopijuoti',
        'codes_copied' => 'Atkūrimo kodai nukopijuoti į iškarpinę',
    ],
    
    // Delete account
    'delete' => [
        'heading' => 'Ištrinti paskyrą',
        'description' => 'Ištrinti savo paskyrą ir visus jos išteklius',
        'warning_title' => 'Įspėjimas',
        'warning_message' => 'Prašome elgtis atsargiai, šio veiksmo negalima atšaukti.',
        'button' => 'Ištrinti paskyrą',
        'confirm_title' => 'Ar tikrai norite ištrinti savo paskyrą?',
        'confirm_description' => 'Kai jūsų paskyra bus ištrinta, visi jos ištekliai ir duomenys taip pat bus visam laikui ištrinti. Prašome įvesti savo slaptažodį, kad patvirtintumėte, jog norite visam laikui ištrinti savo paskyrą.',
        'password' => 'Slaptažodis',
        'password_placeholder' => 'Slaptažodis',
        'cancel' => 'Atšaukti',
        'confirm' => 'Ištrinti paskyrą',
    ],
    
    // Customization
    'customization' => [
        'title' => 'Pritaikymo nustatymai',
        'heading' => 'Pritaikykite savo patirtį',
        'description' => 'Personalizuokite savo programos išvaizdą',
        'save' => 'Išsaugoti pakeitimus',
        'saving' => 'Išsaugoma...',
        'saved' => 'Sėkmingai išsaugota!',
        
        'tabs' => [
            'welcome' => 'Pasveikinimo puslapis',
            'theme' => 'Temos spalvos',
            'branding' => 'Prekės ženklas',
        ],
        
        'welcome' => [
            'title' => 'Pasveikinimo puslapio nustatymai',
            'description' => 'Pritaikykite pasveikinimo puslapio turinį ir išvaizdą',
            'show_page' => 'Rodyti pasveikinimo puslapį',
            'show_page_description' => 'Rodyti pasveikinimo puslapį lankytojams',
            'page_title' => 'Puslapio pavadinimas',
            'page_title_placeholder' => 'Įveskite puslapio pavadinimą',
            'page_subtitle' => 'Puslapio paantraštė',
            'page_subtitle_placeholder' => 'Įveskite puslapio paantraštę',
            'page_description' => 'Puslapio aprašymas',
            'page_description_placeholder' => 'Įveskite puslapio aprašymą',
        ],
        
        'theme' => [
            'title' => 'Spalvų tema',
            'description' => 'Pritaikykite savo programos spalvų schemą',
            'primary_color' => 'Pagrindinė spalva',
            'primary_color_description' => 'Pagrindinė prekės ženklo spalva, naudojama mygtukams ir nuorodoms',
            'secondary_color' => 'Antrinė spalva',
            'secondary_color_description' => 'Palaikomoji spalva akcentams ir išryškinimams',
            'accent_color' => 'Akcento spalva',
            'accent_color_description' => 'Spalva specialiems elementams ir raginimams veikti',
            'color_placeholder' => '#000000',
            'preview' => 'Spalvų peržiūra',
        ],
        
        'branding' => [
            'title' => 'Prekės ženklo tapatybė',
            'description' => 'Konfigūruokite savo prekės ženklo išteklius ir tapatybę',
            'logo_text' => 'Logotipo tekstas',
            'logo_text_placeholder' => 'Įveskite savo prekės ženklo pavadinimą',
            'logo_text_description' => 'Tekstas, rodomas programos logotipe',
            'favicon' => 'Favicon URL',
            'favicon_placeholder' => 'https://pavyzdys.lt/favicon.ico',
            'favicon_description' => 'Jūsų svetainės favicon URL',
            'preview' => 'Logotipo peržiūra',
        ],
        
        'reset' => [
            'title' => 'Atstatyti pritaikymą',
            'description' => 'Atstatyti visus pritaikymo nustatymus į numatytuosius',
            'button' => 'Atstatyti į numatytuosius',
        ],
    ],
];
