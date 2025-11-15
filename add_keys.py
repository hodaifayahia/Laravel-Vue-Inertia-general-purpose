#!/usr/bin/env python3
import json
import os

base_path = '/home/houdaifayahia/www/Laravel-Vue-Inertia-general-purpose'
lang_files = {
    'en': 'lang/php_en.json',
    'ar': 'lang/php_ar.json',
    'fr': 'lang/php_fr.json',
    'lt': 'lang/php_lt.json'
}

# Keys to add
en_keys = {
    "sidebar.main": "Main",
    "about.mission_title": "Our Mission",
    "specialists.page_title": "Our Specialists",
    "contact.page_title": "Contact Us"
}

ar_keys = {
    "sidebar.main": "الرئيسية",
    "about.mission_title": "مهمتنا",
    "specialists.page_title": "متخصصونا",
    "contact.page_title": "اتصل بنا"
}

fr_keys = {
    "sidebar.main": "Accueil",
    "about.mission_title": "Notre Mission",
    "specialists.page_title": "Nos Spécialistes",
    "contact.page_title": "Contactez-nous"
}

lt_keys = {
    "sidebar.main": "Pagrindinis",
    "about.mission_title": "Mūsų Misija",
    "specialists.page_title": "Mūsų Specialistai",
    "contact.page_title": "Susisiekite su mumis"
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

print("\n✓ All translation files updated!")
