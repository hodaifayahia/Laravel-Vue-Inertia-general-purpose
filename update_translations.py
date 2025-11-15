#!/usr/bin/env python3
import json
import os

# Define the paths
base_path = '/home/houdaifayahia/www/Laravel-Vue-Inertia-general-purpose'
lang_files = {
    'en': 'lang/php_en.json',
    'ar': 'lang/php_ar.json',
    'fr': 'lang/php_fr.json',
    'lt': 'lang/php_lt.json'
}

# English translations
en_keys = {
    "welcome.hero_badge": "🇩🇿 Thoughtful by nature. Powerful by design.",
    "welcome.hero_headline": "Empowering Children with Dysgraphia",
    "welcome.hero_description": "Expert therapy and personalized support for writing difficulties. Professional specialists dedicated to unlocking every child's potential across all 58 provinces of Algeria.",
    "welcome.hero_cta_primary": "Start Your Journey",
    "welcome.hero_cta_secondary": "Meet Our Specialists",
    "welcome.specialists_count": "Specialists",
    "welcome.cities_count": "Cities",
    "welcome.provinces_count": "Provinces",
    "welcome.appointments_count": "Appointments",
    "welcome.learn_more": "Learn More",
    "about.understanding_title": "Understanding Dysgraphia",
    "about.understanding_description": "A learning difference that affects writing abilities, but with proper support, individuals can thrive and succeed.",
    "about.what_is_title": "What It Is",
    "about.what_is_description": "Dysgraphia is a neurological condition that affects writing abilities, including handwriting, spelling, and organizing thoughts on paper.",
    "about.signs_title": "Signs & Symptoms",
    "about.signs_description": "Difficulty with letter formation, inconsistent spacing, poor spelling, slow writing speed, and trouble organizing thoughts.",
    "about.how_we_help_title": "How We Help",
    "about.how_we_help_description": "Our specialists provide personalized therapy, strategies, and support to help individuals overcome writing challenges and build confidence.",
    "contact.get_in_touch": "Get In Touch",
    "contact.contact_description": "Have questions? We're here to help you on your journey",
    "contact.phone": "Phone",
    "contact.phone_number": "+213 XXX XXX XXX",
    "contact.email": "Email",
    "contact.email_address": "support@dysgraphia-support.dz",
    "contact.locations": "Locations",
    "contact.locations_description": "Multiple clinics across all 58 provinces",
    "contact.quick_contact": "Quick Contact",
    "contact.name_placeholder": "Your Name",
    "contact.email_placeholder": "Your Email",
    "contact.message_placeholder": "Your Message",
    "contact.send_message": "Send Message",
    "footer.quick_links": "Quick Links",
    "footer.resources": "Resources",
    "footer.connect_with_us": "Connect With Us",
    "footer.find_specialists": "Find Specialists Near You",
    "footer.copyright": "© 2025 Dysgraphia Support Platform. All rights reserved. Made with ❤️ for children in Algeria."
}

# Arabic translations
ar_keys = {
    "welcome.hero_badge": "🇩🇿 مدروس بالطبيعة. قوي بالتصميم.",
    "welcome.hero_headline": "تمكين الأطفال ذوي عسر الكتابة",
    "welcome.hero_description": "العلاج المتخصص والدعم الشخصي لصعوبات الكتابة. متخصصون مكرسون لفتح إمكانات كل طفل عبر 58 ولاية جزائرية.",
    "welcome.hero_cta_primary": "ابدأ رحلتك",
    "welcome.hero_cta_secondary": "تعرف على متخصصينا",
    "welcome.specialists_count": "متخصصون",
    "welcome.cities_count": "مدن",
    "welcome.provinces_count": "ولايات",
    "welcome.appointments_count": "المواعيد",
    "welcome.learn_more": "تعرف على المزيد",
    "about.understanding_title": "فهم عسر الكتابة",
    "about.understanding_description": "اختلاف في التعلم يؤثر على القدرات الكتابية، لكن مع الدعم المناسب، يمكن للأفراد الازدهار والنجاح.",
    "about.what_is_title": "ما هو",
    "about.what_is_description": "عسر الكتابة هو حالة عصبية تؤثر على القدرات الكتابية، بما في ذلك الكتابة اليدوية والإملاء وتنظيم الأفكار على الورق.",
    "about.signs_title": "الإشارات والأعراض",
    "about.signs_description": "صعوبة في تشكيل الحروف، تباعد غير متساو، إملاء ضعيف، سرعة كتابة بطيئة، ومشاكل في تنظيم الأفكار.",
    "about.how_we_help_title": "كيف نساعد",
    "about.how_we_help_description": "يقدم متخصصونا العلاج الشخصي والاستراتيجيات والدعم للمساعدة على التغلب على تحديات الكتابة وبناء الثقة.",
    "contact.get_in_touch": "تواصل معنا",
    "contact.contact_description": "هل لديك أسئلة؟ نحن هنا لمساعدتك في رحلتك",
    "contact.phone": "الهاتف",
    "contact.phone_number": "+213 XXX XXX XXX",
    "contact.email": "البريد الإلكتروني",
    "contact.email_address": "support@dysgraphia-support.dz",
    "contact.locations": "المواقع",
    "contact.locations_description": "عيادات متعددة عبر جميع الولايات الـ 58",
    "contact.quick_contact": "تواصل سريع",
    "contact.name_placeholder": "اسمك",
    "contact.email_placeholder": "بريدك الإلكتروني",
    "contact.message_placeholder": "رسالتك",
    "contact.send_message": "إرسال الرسالة",
    "footer.quick_links": "روابط سريعة",
    "footer.resources": "الموارد",
    "footer.connect_with_us": "تواصل معنا",
    "footer.find_specialists": "ابحث عن متخصصين بالقرب منك",
    "footer.copyright": "© 2025 منصة دعم عسر الكتابة. جميع الحقوق محفوظة. تم الإنشاء بـ ❤️ للأطفال في الجزائر"
}

# French translations
fr_keys = {
    "welcome.hero_badge": "🇩🇿 Réfléchi par nature. Puissant par conception.",
    "welcome.hero_headline": "Autonomiser les enfants atteints de dysgraphie",
    "welcome.hero_description": "Thérapie spécialisée et soutien personnalisé pour les difficultés d'écriture. Des spécialistes dédiés à libérer le potentiel de chaque enfant dans les 58 provinces d'Algérie.",
    "welcome.hero_cta_primary": "Commencez votre voyage",
    "welcome.hero_cta_secondary": "Rencontrez nos spécialistes",
    "welcome.specialists_count": "Spécialistes",
    "welcome.cities_count": "Villes",
    "welcome.provinces_count": "Provinces",
    "welcome.appointments_count": "Rendez-vous",
    "welcome.learn_more": "En savoir plus",
    "about.understanding_title": "Comprendre la dysgraphie",
    "about.understanding_description": "Une différence d'apprentissage qui affecte les capacités d'écriture, mais avec un soutien approprié, les individus peuvent s'épanouir et réussir.",
    "about.what_is_title": "Qu'est-ce que c'est",
    "about.what_is_description": "La dysgraphie est une condition neurologique qui affecte les capacités d'écriture, y compris l'écriture manuscrite, l'orthographe et l'organisation des pensées sur papier.",
    "about.signs_title": "Signes et symptômes",
    "about.signs_description": "Difficulté à former des lettres, espacement irrégulier, orthographe faible, vitesse d'écriture lente et problèmes d'organisation des pensées.",
    "about.how_we_help_title": "Comment nous aidons",
    "about.how_we_help_description": "Nos spécialistes fournissent une thérapie personnalisée, des stratégies et un soutien pour aider à surmonter les défis d'écriture et renforcer la confiance.",
    "contact.get_in_touch": "Contactez-nous",
    "contact.contact_description": "Des questions ? Nous sommes là pour vous aider dans votre parcours",
    "contact.phone": "Téléphone",
    "contact.phone_number": "+213 XXX XXX XXX",
    "contact.email": "Email",
    "contact.email_address": "support@dysgraphia-support.dz",
    "contact.locations": "Emplacements",
    "contact.locations_description": "Plusieurs cliniques dans les 58 provinces",
    "contact.quick_contact": "Contact rapide",
    "contact.name_placeholder": "Votre nom",
    "contact.email_placeholder": "Votre email",
    "contact.message_placeholder": "Votre message",
    "contact.send_message": "Envoyer un message",
    "footer.quick_links": "Liens rapides",
    "footer.resources": "Ressources",
    "footer.connect_with_us": "Connectez-vous avec nous",
    "footer.find_specialists": "Trouvez des spécialistes près de vous",
    "footer.copyright": "© 2025 Plateforme d'assistance dysgraphie. Tous les droits réservés. Créé avec ❤️ pour les enfants d'Algérie"
}

# Lithuanian translations
lt_keys = {
    "welcome.hero_badge": "🇩🇿 Svarbu pagal prigimtį. Galingas pagal dizainą.",
    "welcome.hero_headline": "Suteikti galią vaikams, turintiems disgrafijos",
    "welcome.hero_description": "Specializuota terapija ir asmeninė parama rašymo sunkumams. Specialistai, skirti atskleisti kiekvieno vaiko potencialą visose 58 Alžiro provincijose.",
    "welcome.hero_cta_primary": "Pradėkite savo kelionę",
    "welcome.hero_cta_secondary": "Susitikite su mūsų specialistais",
    "welcome.specialists_count": "Specialistai",
    "welcome.cities_count": "Miestai",
    "welcome.provinces_count": "Provincijos",
    "welcome.appointments_count": "Susitikimai",
    "welcome.learn_more": "Sužinoti daugiau",
    "about.understanding_title": "Disgrafijos supratimas",
    "about.understanding_description": "Mokymosi skirtumas, kuris daro įtaką rašymo gebėjimams, tačiau turėdami tinkamą paramą, žmonės gali klestėti ir sėkmingai tiksus.",
    "about.what_is_title": "Kas tai yra",
    "about.what_is_description": "Disgrafia yra neurologinė būklė, kuri daro įtaką rašymo gebėjimams, įskaitant rašą ranka, rašybą ir minčių organizavimą ant popieriaus.",
    "about.signs_title": "Ženklai ir simptomai",
    "about.signs_description": "Sunkumas formuojant raides, netolygi tarpa, silpna rašyba, lėtas rašymo greitis ir sunkumas organizuojant mintis.",
    "about.how_we_help_title": "Kaip mes padedame",
    "about.how_we_help_description": "Mūsų specialistai teikia asmeninę terapiją, strategijas ir paramą, padedančias įveikti rašymo iššūkius ir stiprinti pasitikėjimą savimi.",
    "contact.get_in_touch": "Susisiekite su mumis",
    "contact.contact_description": "Turite klausimų? Mes čia, kad padėtume jūsų kelyje",
    "contact.phone": "Telefonas",
    "contact.phone_number": "+213 XXX XXX XXX",
    "contact.email": "El. paštas",
    "contact.email_address": "support@dysgraphia-support.dz",
    "contact.locations": "Vietos",
    "contact.locations_description": "Kelios klinikos visose 58 provincijose",
    "contact.quick_contact": "Greitasis kontaktas",
    "contact.name_placeholder": "Jūsų vardas",
    "contact.email_placeholder": "Jūsų el. paštas",
    "contact.message_placeholder": "Jūsų žinutė",
    "contact.send_message": "Siųsti žinutę",
    "footer.quick_links": "Greiti nuorodos",
    "footer.resources": "Ištekliai",
    "footer.connect_with_us": "Susisiekite su mumis",
    "footer.find_specialists": "Raskite specialistus šalia jūsų",
    "footer.copyright": "© 2025 Disgrafijos parama platforma. Visos teisės saugomos. Sukurta su ❤️ Alžiro vaikams"
}

translations_dict = {
    'en': en_keys,
    'ar': ar_keys,
    'fr': fr_keys,
    'lt': lt_keys
}

# Process each language file
for lang, rel_path in lang_files.items():
    file_path = os.path.join(base_path, rel_path)
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            translations = json.load(f)
        
        # Update with new keys
        translations.update(translations_dict[lang])
        
        # Write back
        with open(file_path, 'w', encoding='utf-8') as f:
            json.dump(translations, f, ensure_ascii=False)
        
        print(f"✓ Successfully updated {rel_path}")
    except Exception as e:
        print(f"✗ Error updating {rel_path}: {e}")

print("\n✓ All translation files updated successfully!")
