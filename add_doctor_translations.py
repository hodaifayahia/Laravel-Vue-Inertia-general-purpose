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

# Keys to add for doctors page
en_keys = {
    "doctors.page_title": "Find a Specialist - Dysgraphia Support",
    "doctors.page_heading": "Find Your Specialist",
    "doctors.page_subtitle": "Connect with experienced dysgraphia professionals across Algeria",
    "doctors.search_placeholder": "Search by name, specialty, or location...",
    "doctors.filters_title": "Filters",
    "doctors.clear_filters": "Clear",
    "doctors.province_label": "Province",
    "doctors.province_all": "All Provinces",
    "doctors.city_label": "City",
    "doctors.city_all": "All Cities",
    "doctors.specialty_label": "Specialty",
    "doctors.specialty_all": "All Specialties",
    "doctors.experience_label": "Minimum Experience: {years} years",
    "doctors.experience_label_short": "Minimum Experience",
    "doctors.sort_by_label": "Sort By",
    "doctors.sort_name": "Name (A-Z)",
    "doctors.sort_experience": "Experience (High to Low)",
    "doctors.sort_city": "City (A-Z)",
    "doctors.specialists_found": "specialists found",
    "doctors.no_results": "No specialists found matching your criteria",
    "doctors.loading": "Loading specialists...",
    "doctors.search_by_name": "Search by name",
    "doctors.search_by_specialty": "Search by specialty",
    "doctors.search_by_location": "Search by location",
    "doctors.years_experience": "years of experience",
    "doctors.consultation_fee": "Consultation Fee",
    "doctors.view_profile": "View Profile",
    "doctors.book_appointment": "Book Appointment"
}

ar_keys = {
    "doctors.page_title": "ابحث عن متخصص - دعم عسر الكتابة",
    "doctors.page_heading": "ابحث عن متخصصك",
    "doctors.page_subtitle": "تواصل مع متخصصي عسر الكتابة ذوي الخبرة في جميع أنحاء الجزائر",
    "doctors.search_placeholder": "ابحث حسب الاسم أو التخصص أو الموقع...",
    "doctors.filters_title": "المرشحات",
    "doctors.clear_filters": "مسح",
    "doctors.province_label": "الولاية",
    "doctors.province_all": "جميع الولايات",
    "doctors.city_label": "المدينة",
    "doctors.city_all": "جميع المدن",
    "doctors.specialty_label": "التخصص",
    "doctors.specialty_all": "جميع التخصصات",
    "doctors.experience_label": "الحد الأدنى للخبرة: {years} سنة",
    "doctors.experience_label_short": "الحد الأدنى للخبرة",
    "doctors.sort_by_label": "رتب حسب",
    "doctors.sort_name": "الاسم (أ-ي)",
    "doctors.sort_experience": "الخبرة (الأعلى إلى الأقل)",
    "doctors.sort_city": "المدينة (أ-ي)",
    "doctors.specialists_found": "متخصصون عثرنا عليهم",
    "doctors.no_results": "لم يتم العثور على متخصصين يطابقون معاييرك",
    "doctors.loading": "جاري تحميل المتخصصين...",
    "doctors.search_by_name": "البحث حسب الاسم",
    "doctors.search_by_specialty": "البحث حسب التخصص",
    "doctors.search_by_location": "البحث حسب الموقع",
    "doctors.years_experience": "سنوات خبرة",
    "doctors.consultation_fee": "رسوم الاستشارة",
    "doctors.view_profile": "عرض الملف الشخصي",
    "doctors.book_appointment": "حجز موعد"
}

fr_keys = {
    "doctors.page_title": "Trouver un Spécialiste - Soutien à la Dysgraphie",
    "doctors.page_heading": "Trouvez Votre Spécialiste",
    "doctors.page_subtitle": "Connectez-vous avec des professionnels expérimentés en dysgraphie à travers l'Algérie",
    "doctors.search_placeholder": "Rechercher par nom, spécialité ou localisation...",
    "doctors.filters_title": "Filtres",
    "doctors.clear_filters": "Effacer",
    "doctors.province_label": "Province",
    "doctors.province_all": "Toutes les Provinces",
    "doctors.city_label": "Ville",
    "doctors.city_all": "Toutes les Villes",
    "doctors.specialty_label": "Spécialité",
    "doctors.specialty_all": "Toutes les Spécialités",
    "doctors.experience_label": "Expérience Minimale: {years} ans",
    "doctors.experience_label_short": "Expérience Minimale",
    "doctors.sort_by_label": "Trier par",
    "doctors.sort_name": "Nom (A-Z)",
    "doctors.sort_experience": "Expérience (Plus à Moins)",
    "doctors.sort_city": "Ville (A-Z)",
    "doctors.specialists_found": "spécialistes trouvés",
    "doctors.no_results": "Aucun spécialiste ne correspond à vos critères",
    "doctors.loading": "Chargement des spécialistes...",
    "doctors.search_by_name": "Rechercher par nom",
    "doctors.search_by_specialty": "Rechercher par spécialité",
    "doctors.search_by_location": "Rechercher par localisation",
    "doctors.years_experience": "ans d'expérience",
    "doctors.consultation_fee": "Honoraires de Consultation",
    "doctors.view_profile": "Voir le Profil",
    "doctors.book_appointment": "Prendre Rendez-vous"
}

lt_keys = {
    "doctors.page_title": "Raskite Specialistą - Disgrafijos Parama",
    "doctors.page_heading": "Raskite Savo Specialistą",
    "doctors.page_subtitle": "Susisiekite su patyrusiais disgrafijos specialistais visoje Alžire",
    "doctors.search_placeholder": "Ieškoti pagal vardą, specialybę ar vietą...",
    "doctors.filters_title": "Filtrai",
    "doctors.clear_filters": "Išvalyti",
    "doctors.province_label": "Provincija",
    "doctors.province_all": "Visos Provincijos",
    "doctors.city_label": "Miestas",
    "doctors.city_all": "Visi Miestai",
    "doctors.specialty_label": "Specialybė",
    "doctors.specialty_all": "Visos Specialybės",
    "doctors.experience_label": "Minimali Patirtis: {years} metai",
    "doctors.experience_label_short": "Minimali Patirtis",
    "doctors.sort_by_label": "Rūšiuoti pagal",
    "doctors.sort_name": "Vardas (A-Z)",
    "doctors.sort_experience": "Patirtis (Aukštiausia iki Žemiausia)",
    "doctors.sort_city": "Miestas (A-Z)",
    "doctors.specialists_found": "specialistai rasti",
    "doctors.no_results": "Nerasta specialistų, atitinkančių jūsų kriterijus",
    "doctors.loading": "Kraunami specialistai...",
    "doctors.search_by_name": "Ieškoti pagal vardą",
    "doctors.search_by_specialty": "Ieškoti pagal specialybę",
    "doctors.search_by_location": "Ieškoti pagal vietą",
    "doctors.years_experience": "metų patirtis",
    "doctors.consultation_fee": "Konsultacijos Mokestis",
    "doctors.view_profile": "Peržiūrėti Profilį",
    "doctors.book_appointment": "Suplanuoti Susitikimą"
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

print("\n✓ All doctor page translation keys added!")
