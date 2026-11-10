(async function () {
    'use strict';

    const globalScript = document.currentScript;
    if (globalScript && globalScript.src) {
        await new Promise(resolve => {
            const translationsScript = document.createElement('script');
            translationsScript.src = new URL('youruni-translations.js', globalScript.src).href;
            translationsScript.onload = resolve;
            translationsScript.onerror = resolve;
            document.head.appendChild(translationsScript);
        });
        await new Promise(resolve => {
            const descriptionsScript = document.createElement('script');
            descriptionsScript.src = new URL('university-descriptions.js', globalScript.src).href;
            descriptionsScript.onload = resolve;
            descriptionsScript.onerror = resolve;
            document.head.appendChild(descriptionsScript);
        });
    }

    const dictionary = {
        'tagline': { en: "Explore Myanmar's Universities", my: 'မြန်မာနိုင်ငံရှိ တက္ကသိုလ်များကို လေ့လာပါ' },
        'nav.home': { en: 'Home', my: 'ပင်မစာမျက်နှာ' },
        'nav.universities': { en: 'Universities', my: 'တက္ကသိုလ်များ' },
        'nav.opportunities': { en: 'Opportunities', my: 'အခွင့်အလမ်းများ' },
        'donate.cta': { en: 'Donate Us', my: 'လှူဒါန်းရန်' },
        'donate.homeEyebrow': { en: 'SUPPORTING YOURUNI', my: 'YOURUNI ကို ပံ့ပိုးခြင်း' },
        'donate.homeTitle': { en: 'Help keep YourUni available to students.', my: 'YourUni ကို ကျောင်းသားများ ဆက်လက်အသုံးပြုနိုင်ရန် ကူညီပါ။' },
        'donate.homeCopy': { en: 'If YourUni helps you explore your options, an optional contribution can help with website hosting and improving university information.', my: 'YourUni က သင့်ရွေးချယ်စရာများကို လေ့လာရာတွင် အထောက်အကူဖြစ်ပါက ဝက်ဘ်ဆိုက်ထိန်းသိမ်းမှုနှင့် တက္ကသိုလ်အချက်အလက်များ တိုးတက်စေရန် စိတ်ကြိုက်လှူဒါန်းပံ့ပိုးနိုင်ပါသည်။' },
        'donate.eyebrow': { en: 'DONATE US · SUPPORT EDUCATION ACCESS', my: 'လှူဒါန်းရန် · ပညာရေးအခွင့်အလမ်းများကို ပံ့ပိုးပါ' },
        'donate.title': { en: 'Help students find their next step.', my: 'ကျောင်းသားများ၏ နောက်တစ်ဆင့်ကို ရှာဖွေရန် ကူညီပါ။' },
        'donate.copy': { en: 'YourUni brings Myanmar university information together to make educational choices easier to explore. Support can help maintain the website, improve information quality, and keep the project accessible.', my: 'YourUni သည် မြန်မာနိုင်ငံရှိ တက္ကသိုလ်အချက်အလက်များကို တစ်နေရာတည်းတွင် စုစည်းပေးပါသည်။ ပံ့ပိုးမှုသည် ဝက်ဘ်ဆိုက်ကို ထိန်းသိမ်းရန်၊ အချက်အလက်အရည်အသွေး မြှင့်တင်ရန်နှင့် အသုံးပြုနိုင်မှုကို ဆက်လက်ထိန်းသိမ်းရန် ကူညီနိုင်ပါသည်။' },
        'donate.currentTitle': { en: 'Current donation status', my: 'လက်ရှိ လှူဒါန်းမှုအခြေအနေ' },
        'donate.currentCopy': { en: 'Local QR payments are being prepared. This page is not currently collecting or verifying donations.', my: 'ဒေသတွင်း QR ငွေပေးချေမှုကို ပြင်ဆင်နေဆဲဖြစ်သည်။ ယခုစာမျက်နှာသည် လှူဒါန်းငွေကို လက်ခံခြင်း သို့မဟုတ် အတည်ပြုခြင်း မပြုလုပ်သေးပါ။' },
        'donate.notice': { en: 'A real provider-issued merchant QR must be configured before accepting donations. No sample or placeholder QR is shown, to avoid sending money to the wrong recipient.', my: 'လှူဒါန်းငွေ လက်ခံမီ ဝန်ဆောင်မှုပေးသူမှ ထုတ်ပေးသော တရားဝင် Merchant QR ကို သတ်မှတ်ရမည်။ ငွေမှားယွင်းပို့မိခြင်းကို ကာကွယ်ရန် နမူနာ QR မပြထားပါ။' },
        'donate.transparencyTitle': { en: 'How support will be used', my: 'ပံ့ပိုးမှုကို မည်သို့အသုံးပြုမည်' },
        'donate.transparencyCopy': { en: 'Potential uses include hosting costs, maintaining university records, accessibility improvements, and site development. Donations must remain optional and never affect access to university information.', my: 'ပံ့ပိုးမှုကို Hosting ကုန်ကျစရိတ်၊ တက္ကသိုလ်မှတ်တမ်းများ ထိန်းသိမ်းမှု၊ အသုံးပြုရလွယ်ကူမှုနှင့် ဝက်ဘ်ဆိုက်တိုးတက်မှုတို့အတွက် အသုံးပြုနိုင်ပါသည်။ လှူဒါန်းခြင်းသည် ရွေးချယ်နိုင်သည့်အရာဖြစ်ပြီး တက္ကသိုလ်အချက်အလက်ကို အသုံးပြုခွင့်အပေါ် သက်ရောက်မှု မရှိရပါ။' },
        'donate.privacy': { en: 'YourUni will never ask for a payment PIN, wallet password, or card details on this website.', my: 'YourUni သည် ငွေပေးချေမှု PIN၊ ပိုက်ဆံအိတ်စကားဝှက် သို့မဟုတ် ကတ်အချက်အလက်များကို ဝက်ဘ်ဆိုက်တွင် မတောင်းပါ။' },
        'donate.back': { en: '← Back to About YourUni', my: '← YourUni အကြောင်းသို့ ပြန်သွားရန်' },

        'nav.about': { en: 'About Us', my: 'ကျွန်ုပ်တို့အကြောင်း' },
        'nav.login': { en: 'Admin Login', my: 'စီမံခန့်ခွဲသူ ဝင်ရောက်ရန်' },
        'about.eyebrow': { en: 'ABOUT YOURUNI', my: 'YOURUNI အကြောင်း' },
        'about.heroTitle': { en: "Every student's next chapter starts with the right information.", my: 'ကျောင်းသားတိုင်း၏ နောက်တစ်ဆင့်ခရီးကို မှန်ကန်သော အချက်အလက်များဖြင့် စတင်နိုင်ပါစေ။' },
        'about.heroCopy': { en: 'Choosing a university can feel overwhelming. YourUni brings university information and study fields together in one place, helping students explore their options and make more informed decisions about what comes next.', my: 'တက္ကသိုလ်ရွေးချယ်ခြင်းသည် ခက်ခဲရှုပ်ထွေးနိုင်ပါသည်။ YourUni သည် တက္ကသိုလ်အချက်အလက်များနှင့် ပညာရပ်နယ်ပယ်များကို တစ်နေရာတည်းတွင် စုစည်းပေးပြီး ကျောင်းသားများ မိမိတို့၏ ရွေးချယ်စရာများကို လေ့လာကာ ပိုမိုသိရှိနားလည်သော ဆုံးဖြတ်ချက်များ ချနိုင်ရန် ကူညီပေးပါသည်။' },
        'about.exploreButton': { en: 'Explore universities →', my: 'တက္ကသိုလ်များကို လေ့လာရန် →' },
        'about.fieldsButton': { en: 'Discover study fields', my: 'ပညာရပ်နယ်ပယ်များကို ရှာဖွေရန်' },
        'about.visualTitle': { en: 'Explore your options', my: 'ရွေးချယ်စရာများကို လေ့လာပါ' },
        'about.visualOne': { en: 'Universities', my: 'တက္ကသိုလ်များ' },
        'about.visualTwo': { en: 'Fields of study', my: 'ပညာရပ်နယ်ပယ်များ' },
        'about.visualThree': { en: 'Your next steps', my: 'သင်၏ နောက်တစ်ဆင့်များ' },
        'about.whyKicker': { en: 'WHAT YOU CAN DO', my: 'သင်လုပ်ဆောင်နိုင်သည်များ' },
        'about.whyTitle': { en: 'Find the options that fit your plans.', my: 'သင့်အစီအစဉ်နှင့် ကိုက်ညီသော ရွေးချယ်စရာများကို ရှာဖွေပါ။' },
        'about.whyCopy': { en: 'Browse universities by region and field, review study options, and compare campuses. Admissions details can change, so confirm current requirements with each institution.', my: 'ဒေသနှင့် ပညာရပ်နယ်ပယ်အလိုက် တက္ကသိုလ်များကို ကြည့်ရှုပြီး ရွေးချယ်စရာများကို နှိုင်းယှဉ်ပါ။ ဝင်ခွင့်အချက်အလက်များ ပြောင်းလဲနိုင်သဖြင့် သက်ဆိုင်ရာ တက္ကသိုလ်ထံ အတည်ပြုပါ။' },
        'about.missionTitle': { en: 'Why we built YourUni', my: 'YourUni ကို ဖန်တီးရခြင်း' },
        'about.missionCopy': { en: 'University information is spread across many sources. YourUni brings key details together so students can browse their options in one place.', my: 'တက္ကသိုလ်အချက်အလက်များကို ရင်းမြစ်အမျိုးမျိုးတွင် ရှာဖွေရသည်။ YourUni က အဓိကအချက်အလက်များကို တစ်နေရာတည်းတွင် စုစည်းပေးသည်။' },
        'about.visionTitle': { en: 'What to expect', my: 'သိထားသင့်သည်များ' },
        'about.visionCopy': { en: "Use the directory to discover campuses, then check each university's official channels for the latest programs and admissions information.", my: 'တက္ကသိုလ်များကို ရှာဖွေပြီးနောက် လက်ရှိသင်တန်းနှင့် ဝင်ခွင့်အချက်အလက်များကို တရားဝင်ရင်းမြစ်များတွင် စစ်ဆေးပါ။' },
        'about.howKicker': { en: 'YOURUNI DIRECTORY', my: 'YOURUNI တက္ကသိုလ်စာရင်း' },
        'about.howTitle': { en: 'Start with what matters to you.', my: 'သင်စိတ်ဝင်စားရာမှ စတင်ပါ။' },
        'about.howCopy': { en: 'Start with a field or region, open campus pages for details, and make a shortlist to investigate further.', my: 'ပညာရပ်နယ်ပယ် သို့မဟုတ် ဒေသတစ်ခုမှ စတင်ပြီး တက္ကသိုလ်စာမျက်နှာများကို ကြည့်ကာ ထပ်မံလေ့လာရန် စာရင်းပြုစုပါ။' },
        'about.featureOneTitle': { en: 'Browse campuses', my: 'တက္ကသိုလ်များကို ကြည့်ရှုပါ' },
        'about.featureOneCopy': { en: 'Search by name, region, or field.', my: 'အမည်၊ ဒေသ သို့မဟုတ် ပညာရပ်ဖြင့် ရှာဖွေပါ။' },
        'about.featureTwoTitle': { en: 'Explore study areas', my: 'ပညာရပ်နယ်ပယ်များကို လေ့လာပါ' },
        'about.featureTwoCopy': { en: 'Find related universities and career paths.', my: 'သက်ဆိုင်ရာ တက္ကသိုလ်များနှင့် အလုပ်အကိုင်လမ်းကြောင်းများကို ရှာဖွေပါ။' },
        'about.featureThreeTitle': { en: 'Review your options', my: 'ရွေးချယ်စရာများကို ကြည့်ရှုပါ' },
        'about.featureThreeCopy': { en: 'Compare campuses and check official admissions details.', my: 'တက္ကသိုလ်များကို နှိုင်းယှဉ်ပြီး တရားဝင် ဝင်ခွင့်အချက်အလက်များကို စစ်ဆေးပါ။' },
        'about.valuesKicker': { en: 'OUR APPROACH', my: 'ကျွန်ုပ်တို့၏ လုပ်ဆောင်ပုံ' },
        'about.valuesTitle': { en: 'Clear information. Your choice.', my: 'ရှင်းလင်းသော အချက်အလက်။ သင့်ရွေးချယ်မှု။' },
        'about.valuesCopy': { en: 'We aim to make the options easier to browse while leaving the decisions, priorities, and next steps to you.', my: 'ရွေးချယ်စရာများကို လွယ်ကူစွာ ကြည့်ရှုနိုင်စေရန် ရည်ရွယ်ပြီး ဆုံးဖြတ်ချက်နှင့် နောက်တစ်ဆင့်ကို သင်ကိုယ်တိုင် ရွေးချယ်နိုင်သည်။' },
        'about.valueOneTitle': { en: 'Straightforward', my: 'ရိုးရှင်းမှု' },
        'about.valueOneCopy': { en: 'Keep pages readable and easy to browse.', my: 'စာမျက်နှာများကို ဖတ်ရှုကြည့်ရှုရလွယ်ကူစေခြင်း။' },
        'about.valueTwoTitle': { en: 'Accessible', my: 'လက်လှမ်းမီမှု' },
        'about.valueTwoCopy': { en: 'Support different devices and language preferences.', my: 'စက်အမျိုးမျိုးနှင့် ဘာသာစကားရွေးချယ်မှုများကို ပံ့ပိုးခြင်း။' },
        'about.valueThreeTitle': { en: 'Open-ended', my: 'ရွေးချယ်စရာများ' },
        'about.valueThreeCopy': { en: 'Make room to explore more than one path.', my: 'လမ်းကြောင်းတစ်ခုထက်ပို၍ လေ့လာနိုင်စေခြင်း။' },
        'about.valueFourTitle': { en: 'Careful', my: 'အတည်ပြုမှု' },
        'about.valueFourCopy': { en: 'Check current admissions details with official sources.', my: 'လက်ရှိဝင်ခွင့်အချက်အလက်များကို တရားဝင်ရင်းမြစ်များတွင် စစ်ဆေးပါ။' },
        'about.closingTitle': { en: 'Ready to look around?', my: 'စတင်လေ့လာရန် အသင့်ဖြစ်ပြီလား။' },
        'about.closingCopy': { en: 'Browse the directory or start with a field that interests you.', my: 'တက္ကသိုလ်စာရင်းကို ကြည့်ရှုပါ သို့မဟုတ် စိတ်ဝင်စားသော ပညာရပ်နယ်ပယ်မှ စတင်ပါ။' },
        'about.closingButton': { en: 'Browse universities →', my: 'တက္ကသိုလ်များကို ကြည့်ရှုရန် →' },
        'about.footer': { en: 'Explore thoughtfully. Verify official admission details.', my: 'သေချာစွာ လေ့လာပြီး တရားဝင် ဝင်ခွင့်အချက်အလက်များကို အတည်ပြုပါ။' },
        'Home': { en: 'Home', my: 'ပင်မစာမျက်နှာ' },
        'Universities': { en: 'Universities', my: 'တက္ကသိုလ်များ' },
        'Opportunities': { en: 'Opportunities', my: 'အခွင့်အလမ်းများ' },
        'About Us': { en: 'About Us', my: 'ကျွန်ုပ်တို့အကြောင်း' },
        'Search': { en: 'Search', my: 'ရှာဖွေရန်' },
        'All Universities': { en: 'All Universities', my: 'တက္ကသိုလ်အားလုံး' },
        'Search Results': { en: 'Search Results', my: 'ရှာဖွေမှုရလဒ်များ' },
        'Featured Universities': { en: 'Featured Universities', my: 'အထူးဖော်ပြထားသော တက္ကသိုလ်များ' },
        'View All Universities →': { en: 'View All Universities →', my: 'တက္ကသိုလ်အားလုံး ကြည့်ရန် →' },
        'No universities found': { en: 'No universities found', my: 'တက္ကသိုလ် မတွေ့ရှိပါ' },
        'No score-eligible universities found': { en: 'No score-eligible universities found', my: 'ရမှတ်နှင့် ကိုက်ညီသော တက္ကသိုလ် မတွေ့ရှိပါ' },
        'Home': { en: 'Home', my: 'ပင်မစာမျက်နှာ' },
        'Any Major': { en: 'Any Major', my: 'မည်သည့် မေဂျာမဆို' },
        'Any Region': { en: 'Any Region', my: 'မည်သည့် ဒေသမဆို' },
        'Major': { en: 'Major', my: 'မေဂျာ' },
        'Region': { en: 'Region', my: 'ဒေသ' },
        'Marks': { en: 'Marks', my: 'ရမှတ်' },
        'Engineering': { en: 'Engineering', my: 'အင်ဂျင်နီယာ' },
        'Medicine': { en: 'Medicine', my: 'ဆေးပညာ' },
        'Computer Science': { en: 'Computer Science', my: 'ကွန်ပျူတာသိပ္ပံ' },
        'Arts & Science': { en: 'Arts & Science', my: 'ဝိဇ္ဇာနှင့် သိပ္ပံ' },
        'Economics': { en: 'Economics', my: 'ဘောဂဗေဒ' },
        'Foreign Languages': { en: 'Foreign Languages', my: 'နိုင်ငံခြားဘာသာစကားများ' },
        'Clear Search': { en: 'Clear Search', my: 'ရှာဖွေမှုကို ရှင်းလင်းရန်' },
        'Best matches for your selected field': { en: 'Best matches for your selected field', my: 'ရွေးချယ်ထားသော ပညာရပ်နှင့် အကိုက်ညီဆုံးများ' },
        'General university options': { en: 'General university options', my: 'အထွေထွေ တက္ကသိုလ်ရွေးချယ်စရာများ' },
        'Other score-eligible universities': { en: 'Other score-eligible universities', my: 'ရမှတ်နှင့် ကိုက်ညီသော အခြားတက္ကသိုလ်များ' },
        'Admin Portal Login': { en: 'Admin Portal Login', my: 'စီမံခန့်ခွဲသူ ဝင်ရောက်ရန်' },
        'Username': { en: 'Username', my: 'အသုံးပြုသူအမည်' },
        'Password': { en: 'Password', my: 'စကားဝှက်' },
        'Log In': { en: 'Log In', my: 'ဝင်ရောက်ရန်' },
        'Logout': { en: 'Logout', my: 'ထွက်ရန်' },
        'Sign out': { en: 'Sign out', my: 'ထွက်ရန်' },
        'Publish University': { en: 'Publish University', my: 'တက္ကသိုလ်ကို ထည့်သွင်းရန်' },
        'Save Changes': { en: 'Save Changes', my: 'ပြောင်းလဲမှုများ သိမ်းရန်' },
        'Add Opportunity': { en: 'Add Opportunity', my: 'အခွင့်အလမ်း ထည့်ရန်' },
        'Explore Career Paths': { en: 'Explore Career Paths', my: 'အလုပ်အကိုင်လမ်းကြောင်းများကို လေ့လာရန်' },
        'specializations.heading': { en: 'Majors & Specializations', my: 'မေဂျာများနှင့် အထူးပြုဘာသာရပ်များ' },
        'specializations.intro': { en: 'Study areas published by the YourUni administration. University associations are labelled according to their verification status.', my: 'YourUni စီမံခန့်ခွဲသူများ ထည့်သွင်းထားသော ပညာရပ်နယ်ပယ်များဖြစ်သည်။ တက္ကသိုလ်နှင့် ဆက်စပ်မှုများကို အတည်ပြုမှုအခြေအနေအလိုက် ဖော်ပြထားသည်။' },
        'specializations.studyLabel': { en: 'What you will study', my: 'သင်ကြားလေ့လာရမည့် အကြောင်းအရာ' },
        'specializations.contextLabel': { en: 'Related pathways / context', my: 'ဆက်စပ်လမ်းကြောင်းများ / အကြောင်းအရာ' },
        'specializations.empty': { en: 'No specializations are published for this category yet.', my: 'ဤအမျိုးအစားအတွက် အထူးပြုဘာသာရပ်များ မထည့်သွင်းရသေးပါ။' },
        'Universities for this Field': { en: 'Universities for this Field', my: 'ဤနယ်ပယ်အတွက် တက္ကသိုလ်များ' },
        'Click a career card to highlight universities offering or related to that program. Unconfirmed entries are not highlighted; verify the official program list before applying.': { en: 'Click a career card to highlight universities offering or related to that program. Unconfirmed entries are not highlighted; verify the official program list before applying.', my: 'သက်ဆိုင်ရာ ပညာရပ်နှင့် ကိုက်ညီနိုင်သော တက္ကသိုလ်များကို မီးမောင်းထိုးပြရန် အလုပ်အကိုင်ကတ်ကို နှိပ်ပါ။ အတည်မပြုရသေးသော တက္ကသိုလ်များကို မီးမောင်းထိုးပြထားခြင်း မရှိပါ။ လျှောက်ထားမီ တရားဝင် ပညာရပ်စာရင်းကို စစ်ဆေးပါ။' },
        'Show all universities': { en: 'Show all universities', my: 'တက္ကသိုလ်အားလုံး ပြရန်' },
        'Light': { en: 'Light', my: 'အလင်း' },
        'Dark': { en: 'Dark', my: 'အမှောင်' },
        'System': { en: 'System', my: 'စနစ်အတိုင်း' },
        'Engineering & Tech': { en: 'Engineering & Tech', my: 'အင်ဂျင်နီယာနှင့် နည်းပညာ' },
        'Computer Science & IT': { en: 'Computer Science & IT', my: 'ကွန်ပျူတာသိပ္ပံနှင့် IT' },
        'Medicine & Healthcare': { en: 'Medicine & Healthcare', my: 'ဆေးပညာနှင့် ကျန်းမာရေးစောင့်ရှောက်မှု' },
        'Business & Management': { en: 'Business & Management', my: 'စီးပွားရေးနှင့် စီမံခန့်ခွဲမှု' },
        'Foreign Languages': { en: 'Foreign Languages', my: 'နိုင်ငံခြားဘာသာစကားများ' },
        'Law & Governance': { en: 'Law & Governance', my: 'ဥပဒေနှင့် အုပ်ချုပ်ရေး' },
        'Agriculture & Forestry': { en: 'Agriculture & Forestry', my: 'စိုက်ပျိုးရေးနှင့် သစ်တောပညာ' },
        'Education & Pedagogy': { en: 'Education & Pedagogy', my: 'ပညာရေးနှင့် သင်ကြားရေးပညာ' },
        'Arts & Culture': { en: 'Arts & Culture', my: 'အနုပညာနှင့် ယဉ်ကျေးမှု' },
        'Arts & Science': { en: 'Arts & Science', my: 'ဝိဇ္ဇာနှင့် သိပ္ပံ' },
        'Defense & Military': { en: 'Defense & Military', my: 'ကာကွယ်ရေးနှင့် စစ်ဘက်ဆိုင်ရာ' },
        '✨ Career Pathways': { en: '✨ Career Pathways', my: '✨ အလုပ်အကိုင်လမ်းကြောင်းများ' },
        'Explore Fields of Study': { en: 'Explore Fields of Study', my: 'ပညာရပ်နယ်ပယ်များကို လေ့လာပါ' },
        'Select a field to discover related university degrees, prospective job roles, and scholarship opportunities.': { en: 'Select a field to discover related university degrees, prospective job roles, and scholarship opportunities.', my: 'သက်ဆိုင်ရာ ဘွဲ့အစီအစဉ်များ၊ ဖြစ်နိုင်သော အလုပ်အကိုင်များနှင့် ပညာသင်ဆုအခွင့်အလမ်းများကို လေ့လာရန် နယ်ပယ်တစ်ခုကို ရွေးချယ်ပါ။' },
        'Explore Careers': { en: 'Explore Careers', my: 'အလုပ်အကိုင်များကို လေ့လာရန်' },
        'Designing, building, and maintaining structures, machines, and technological systems.': { en: 'Designing, building, and maintaining structures, machines, and technological systems.', my: 'အဆောက်အအုံများ၊ စက်ပစ္စည်းများနှင့် နည်းပညာစနစ်များကို ဒီဇိုင်းဆွဲခြင်း၊ တည်ဆောက်ခြင်းနှင့် ထိန်းသိမ်းခြင်း။' },
        'Software development, artificial intelligence, cybersecurity, and data science.': { en: 'Software development, artificial intelligence, cybersecurity, and data science.', my: 'ဆော့ဖ်ဝဲဖွံ့ဖြိုးရေး၊ ဉာဏ်ရည်တုနည်းပညာ၊ ဆိုက်ဘာလုံခြုံရေးနှင့် ဒေတာသိပ္ပံ။' },
        'Clinical practice, surgical medicine, nursing, public health, and pharmacy.': { en: 'Clinical practice, surgical medicine, nursing, public health, and pharmacy.', my: 'ဆေးကုသမှု၊ ခွဲစိတ်ဆေးပညာ၊ သူနာပြု၊ ပြည်သူ့ကျန်းမာရေးနှင့် ဆေးဝါးပညာ။' },
        'Financial management, digital marketing, economics, and business administration.': { en: 'Financial management, digital marketing, economics, and business administration.', my: 'ဘဏ္ဍာရေးစီမံခန့်ခွဲမှု၊ ဒစ်ဂျစ်တယ်စျေးကွက်ရှာဖွေရေး၊ ဘောဂဗေဒနှင့် စီးပွားရေးစီမံခန့်ခွဲမှု။' },
        'International relations, translation, diplomacy, and global business trade.': { en: 'International relations, translation, diplomacy, and global business trade.', my: 'နိုင်ငံတကာဆက်ဆံရေး၊ ဘာသာပြန်ခြင်း၊ သံတမန်ရေးရာနှင့် ကမ္ဘာလုံးဆိုင်ရာ ကုန်သွယ်ရေး။' },
        'Legal frameworks, public policy, human rights, and corporate law compliance.': { en: 'Legal frameworks, public policy, human rights, and corporate law compliance.', my: 'ဥပဒေမူဘောင်များ၊ ပြည်သူ့မူဝါဒ၊ လူ့အခွင့်အရေးနှင့် ကုမ္ပဏီဥပဒေလိုက်နာမှု။' },
        'Cultivate the future through agritech, animal husbandry, forest management, and environmental conservation.': { en: 'Cultivate the future through agritech, animal husbandry, forest management, and environmental conservation.', my: 'စိုက်ပျိုးရေးနည်းပညာ၊ တိရစ္ဆာန်မွေးမြူရေး၊ သစ်တောစီမံခန့်ခွဲမှုနှင့် ပတ်ဝန်းကျင်ထိန်းသိမ်းရေးတို့ဖြင့် အနာဂတ်ကို တည်ဆောက်ပါ။' },
        'Shape the next generation with specialized teacher training, curriculum development, and educational administration.': { en: 'Shape the next generation with specialized teacher training, curriculum development, and educational administration.', my: 'ဆရာအတတ်ပညာသင်ကြားရေး၊ သင်ရိုးညွှန်းတမ်းဖွံ့ဖြိုးရေးနှင့် ပညာရေးစီမံခန့်ခွဲမှုတို့ဖြင့် မျိုးဆက်သစ်ကို ပုံဖော်ပါ။' },
        'Explore creative disciplines including visual arts, traditional performance, theater, cinematography, and heritage.': { en: 'Explore creative disciplines including visual arts, traditional performance, theater, cinematography, and heritage.', my: 'ရုပ်ပုံအနုပညာ၊ ရိုးရာဖျော်ဖြေမှု၊ ပြဇာတ်၊ ရုပ်ရှင်နှင့် ယဉ်ကျေးမှုအမွေအနှစ်များကို လေ့လာပါ။' },
        'Study foundational pure sciences like Physics and Chemistry alongside vital humanities like History and Psychology.': { en: 'Study foundational pure sciences like Physics and Chemistry alongside vital humanities like History and Psychology.', my: 'ရူပဗေဒ၊ ဓာတုဗေဒကဲ့သို့ အခြေခံသိပ္ပံပညာများနှင့် သမိုင်း၊ စိတ်ပညာကဲ့သို့ လူမှုလူသားပညာရပ်များကို လေ့လာပါ။' },
        'Engage in specialized military training tracks focusing on command, military engineering, and military healthcare.': { en: 'Engage in specialized military training tracks focusing on command, military engineering, and military healthcare.', my: 'ကွပ်ကဲမှု၊ စစ်ဘက်အင်ဂျင်နီယာနှင့် စစ်ဘက်ကျန်းမာရေးစောင့်ရှောက်မှုတို့ကို အဓိကထားသော အထူးပြုစစ်သင်တန်းလမ်းကြောင်းများကို လေ့လာပါ။' },
        'University Catalogue': { en: 'University Catalogue', my: 'တက္ကသိုလ်စာရင်း' },
        'Homepage Featured': { en: 'Homepage Featured', my: 'ပင်မစာမျက်နှာ အထူးဖော်ပြမှု' },
        'Homepage featured cards': { en: 'Homepage featured cards', my: 'ပင်မစာမျက်နှာ အထူးကတ်များ' },
        'Add to university catalogue': { en: 'Add to university catalogue', my: 'တက္ကသိုလ်စာရင်းသို့ ထည့်ရန်' },
        'Opportunities': { en: 'Opportunities', my: 'အခွင့်အလမ်းများ' },
        'Edit': { en: 'Edit', my: 'ပြင်ဆင်ရန်' },
        'Delete': { en: 'Delete', my: 'ဖျက်ရန်' },
        'Published': { en: 'Published', my: 'ထုတ်ပြန်ထားသည်' },
        'Hidden': { en: 'Hidden', my: 'ဝှက်ထားသည်' },
    };

    const manualTranslations = window.YOURUNI_TRANSLATIONS || {};
    Object.keys(manualTranslations).forEach(key => {
        const existing = dictionary[key] || {};
        const manual = manualTranslations[key];
        dictionary[key] = {
            ...existing,
            ...manual,
            my: manual.my || existing.my || ''
        };
    });

    const exactTranslations = {};
    Object.keys(dictionary).forEach(key => {
        const record = dictionary[key];
        if (record && record.en) exactTranslations[record.en.trim()] = record;
    });
    Object.keys(manualTranslations).forEach(key => {
        const record = manualTranslations[key];
        if (record && record.en) {
            const text = record.en.trim();
            const existing = exactTranslations[text] || {};
            exactTranslations[text] = { ...existing, ...record, my: record.my || existing.my || '' };
        }
    });
    const originalNodes = new WeakMap();
    const originalAttributes = new WeakMap();
    const originalPageTitle = document.title;
    let currentLanguage = 'my';

    function safeGet(key, fallback) {
        try { const v = localStorage.getItem(key); return v || fallback; } catch (_) { return fallback; }
    }
    function safeSet(key, value) {
        try { localStorage.setItem(key, value); } catch (_) { /* storage can be unavailable in private mode */ }
    }
    function normalizeText(value) { return String(value || '').replace(/\s+/g, ' ').trim(); }

    function translateTextNodes(language) {
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
            acceptNode(node) {
                const parent = node.parentElement;
                if (!parent || parent.closest('script,style,noscript,textarea,input,select,option,code,pre,.yu-preferences,[data-i18n],.homepage h1')) return NodeFilter.FILTER_REJECT;
                if (!normalizeText(node.nodeValue)) return NodeFilter.FILTER_REJECT;
                return NodeFilter.FILTER_ACCEPT;
            }
        });
        let node;
        while ((node = walker.nextNode())) {
            if (!originalNodes.has(node)) originalNodes.set(node, node.nodeValue);
            const original = originalNodes.get(node);
            const leading = (original.match(/^\s*/) || [''])[0];
            const trailing = (original.match(/\s*$/) || [''])[0];
            const key = normalizeText(original);
            const item = exactTranslations[key];
            let translated = '';
            if (language === 'my' && item && item.my) translated = item.my;
            else if (language === 'my') {
                let match = key.match(/^(\d+) fields on the board$/);
                if (match) translated = match[1] + ' ပညာရပ်နယ်ပယ်များ';
                match = key.match(/^📍?\s*Campus Location:\s*(.+)$/);
                if (match) translated = '📍 တည်နေရာ: ' + match[1];
                match = key.match(/^Showing (\d+) of (\d+) listings$/);
                if (match) translated = 'စုစုပေါင်း ' + match[2] + ' ခုအနက် ' + match[1] + ' ခုကို ပြသနေသည်';
            }
            node.nodeValue = translated ? leading + translated + trailing : original;
        }
    }

    function translateAttributes(language) {
        const attributeMap = {
            'Search': 'ရှာဖွေရန်',
            'Search universities': 'တက္ကသိုလ်များကို ရှာဖွေရန်',
            'Enter university name': 'တက္ကသိုလ်အမည် ရိုက်ထည့်ပါ',
            'Search by name': 'အမည်ဖြင့် ရှာဖွေရန်'
        };
        document.querySelectorAll('[placeholder],[title],[aria-label]').forEach(el => {
            ['placeholder', 'title', 'aria-label'].forEach(attr => {
                if (!el.hasAttribute(attr)) return;
                if (!originalAttributes.has(el)) originalAttributes.set(el, {});
                const originals = originalAttributes.get(el);
                if (originals[attr] === undefined) originals[attr] = el.getAttribute(attr);
                const initial = originals[attr];
                const translation = exactTranslations[normalizeText(initial)];
                if (language === 'my') el.setAttribute(attr, (translation && translation.my) || attributeMap[initial] || initial);
                else el.setAttribute(attr, initial);
            });
        });
        document.querySelectorAll('option').forEach(option => {
            if (!originalAttributes.has(option)) originalAttributes.set(option, {});
            const originals = originalAttributes.get(option);
            if (originals.optionText === undefined) originals.optionText = option.textContent;
            const translation = exactTranslations[normalizeText(originals.optionText)];
            option.textContent = language === 'my' && translation && translation.my ? translation.my : originals.optionText;
        });
    }

    function translateKeyElements(language) {
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const record = dictionary[key];
            if (!record) return;
            if (!originalAttributes.has(el)) originalAttributes.set(el, {});
            const originals = originalAttributes.get(el);
            if (originals.i18nHTML === undefined) originals.i18nHTML = el.innerHTML;
            if (language === 'en') el.innerHTML = originals.i18nHTML;
            else if (record.my !== undefined && record.my !== '') el.textContent = record.my;
            else el.innerHTML = originals.i18nHTML;
        });
    }

    function setupUniversityDescription() {
        const descriptions = window.YOURUNI_UNIVERSITY_DESCRIPTIONS || {};
        const fileName = window.location.pathname.split('/').pop();
        const description = descriptions[fileName];
        const element = document.querySelector('.uni-header .uni-info > p');
        if (!description || !element) return;
        element.setAttribute('data-yu-en', description.en);
        element.setAttribute('data-yu-my', description.my);
    }

    function applyLanguage(language) {
        currentLanguage = language === 'my' ? 'my' : 'en';
        document.documentElement.lang = currentLanguage === 'my' ? 'my' : 'en';
        translateKeyElements(currentLanguage);
        translateTextNodes(currentLanguage);
        translateAttributes(currentLanguage);
        const pageTitleTranslation = exactTranslations[normalizeText(originalPageTitle)];
        document.title = currentLanguage === 'my' && pageTitleTranslation && pageTitleTranslation.my
            ? pageTitleTranslation.my
            : originalPageTitle;
        document.querySelectorAll('[data-yu-en]').forEach(el => {
            const english = el.getAttribute('data-yu-en') || '';
            const burmese = el.getAttribute('data-yu-my') || '';
            if (currentLanguage === 'en') el.textContent = english;
            else if (burmese.trim()) el.textContent = burmese;
            // When Burmese content was not entered in the admin form, preserve any
            // dictionary translation; otherwise the English source remains as fallback.
        });
        const toggle = document.getElementById('yu-language-toggle');
        if (toggle) {
            toggle.textContent = currentLanguage === 'en' ? 'မြန်မာ' : 'English';
            toggle.setAttribute('aria-label', currentLanguage === 'en' ? 'Switch to Burmese' : 'Switch to English');
            toggle.title = currentLanguage === 'en' ? 'Switch to Burmese' : 'Switch to English';
        }
        const themeSelect = document.getElementById('yu-theme-select');
        if (themeSelect && themeSelect.options.length >= 3) {
            const labels = currentLanguage === 'my'
                ? ['စနစ်အတိုင်း', 'အလင်း', 'အမှောင်']
                : ['System', 'Light', 'Dark'];
            Array.from(themeSelect.options).forEach((option, index) => {
                if (labels[index]) option.textContent = labels[index];
            });
            themeSelect.setAttribute('aria-label', currentLanguage === 'my' ? 'အပြင်အဆင်' : 'Theme');
            themeSelect.title = currentLanguage === 'my' ? 'အပြင်အဆင် ရွေးချယ်ရန်' : 'Choose theme';
        }
        safeSet('youruni-language-choice', currentLanguage);
    }

    function applyTheme(mode) {
        const valid = ['system', 'light', 'dark'].includes(mode) ? mode : 'system';
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const effective = valid === 'system' ? (prefersDark ? 'dark' : 'light') : valid;
        document.documentElement.setAttribute('data-theme', effective);
        document.documentElement.setAttribute('data-theme-mode', valid);
        const select = document.getElementById('yu-theme-select');
        if (select) select.value = valid;
        safeSet('youruni-theme', valid);
    }

    function createControls() {
        if (document.getElementById('yu-preferences')) return;
        const panel = document.createElement('div');
        panel.className = 'yu-preferences';
        panel.id = 'yu-preferences';
        panel.setAttribute('aria-label', 'YourUni display preferences');
        panel.innerHTML = '<label for="yu-theme-select" class="yu-sr-only" data-i18n="Theme">Theme</label><select id="yu-theme-select" aria-label="Theme"><option value="system">System</option><option value="light">Light</option><option value="dark">Dark</option></select><button id="yu-language-toggle" type="button" aria-label="Switch language">မြန်မာ</button>';
        document.body.appendChild(panel);
        const select = document.getElementById('yu-theme-select');
        select.addEventListener('change', () => applyTheme(select.value));
        document.getElementById('yu-language-toggle').addEventListener('click', () => applyLanguage(currentLanguage === 'en' ? 'my' : 'en'));
    }

    function setupHomeNotice() {
        const notice = document.getElementById('yu-site-notice');
        const dismiss = document.getElementById('yu-site-notice-dismiss');
        if (!notice || !dismiss) return;

        let alreadyDismissed = false;
        try { alreadyDismissed = window.sessionStorage.getItem('youruni-home-notice-dismissed') === '1'; } catch (_) { /* Continue without session persistence when storage is blocked. */ }
        if (alreadyDismissed) return;

        const markDismissed = () => {
            try { window.sessionStorage.setItem('youruni-home-notice-dismissed', '1'); } catch (_) { /* The notice still closes when storage is blocked. */ }
        };
        dismiss.addEventListener('click', () => notice.close());
        notice.addEventListener('close', markDismissed, { once: true });
        if (typeof notice.showModal === 'function') notice.showModal();
    }

    function programSlug(value) {
        return normalizeText(value).toLowerCase().replace(/&/g, 'and').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    async function setupSpecialtyFilter() {
        const list = document.querySelector('.uni-tags');
        if (!list) return;

        const pathOf = a => {
            const raw = (a.getAttribute('href') || '').split(/[?#]/)[0];
            return raw.split('/').pop().toLowerCase();
        };
        const universityLinks = Array.from(list.querySelectorAll('a.uni-badge'));
        const originalUniversityOrder = universityLinks.slice();
        if (!universityLinks.length) return;

        // Starter mapping for the student's existing directory. Verify individual programs with
        // official university prospectuses before using these results for real admissions decisions.
        const known = {
            engineering: {
                'civil-engineering': ['ytu.html','mtu.html','nspu.html','wytu.html','tuthanlyin.html','tumandalay.html','tumonywa.html','tukalay.html','tukyaukse.html','tumeiktila.html','tumagway.html','tupakokku.html','tutaunggyi.html','tuloikaw.html','tusittway.html','tupyay.html','tuhmawbi.html','tupathein.html','tuhinthada.html','tuhpaan.html','tumawlamyine.html','tudawei.html','tumyeik.html','tulashio.html','tupanglong.html','tusagaing.html','tutaungoo.html','tuyamethin.html','tumyitkyina.html','pupanglong.html','pukyaingtong.html','pumyeik.html','pudawei.html','pumaubin.html','pumyitkyina.html','tubhamo.html','tukyaingtong.html','tumaubin.html'],
                'electronic-engineering': ['ytu.html','mtu.html','utycc.html','nspu.html','wytu.html','tuthanlyin.html','tumandalay.html','tumonywa.html','tukalay.html','tukyaukse.html','tumeiktila.html','tuhmawbi.html','tusagaing.html','dsta.html'],
                'electrical-power-engineering': ['ytu.html','mtu.html','nspu.html','wytu.html','tuthanlyin.html','tumandalay.html','tumonywa.html','tukalay.html','tukyaukse.html','tumeiktila.html','tumagway.html','tupakokku.html','tutaunggyi.html','tuloikaw.html','tusittway.html','tupyay.html','tuhmawbi.html','tupathein.html','tuhinthada.html','tuhpaan.html','tumawlamyine.html','tudawei.html','tumyeik.html','tulashio.html','tupanglong.html','tusagaing.html','tutaungoo.html'],
                'mechanical-engineering': ['ytu.html','mtu.html','nspu.html','mmu.html','plane.html','wytu.html','tuthanlyin.html','tumandalay.html','tumonywa.html','tumeiktila.html','tuhmawbi.html','tudawei.html','tumyeik.html','tusagaing.html','dsta.html'],
                'telecommunication-engineering': ['ytu.html','mtu.html','utycc.html','nspu.html','wytu.html','tuthanlyin.html','tumandalay.html','tuhmawbi.html','dsta.html'],
                'computer-engineering-and-it': ['ytu.html','mtu.html','utycc.html','nspu.html','miit.html','uit.html','ucsy.html','ucsm.html','wytu.html','tuthanlyin.html','tumandalay.html','dsta.html'],
                architecture: ['ytu.html','mtu.html'],
                'naval-architecture': ['mmu.html'],
                'marine-engineering': ['mmu.html'],
                'port-and-harbour-engineering': ['mmu.html'],
                'river-and-coastal-engineering': ['mmu.html'],
                'marine-electrical-and-electronics-engineering': ['mmu.html'],
                'marine-mechanical-engineering': ['mmu.html'],
                'nautical-science': ['mmu.html'],
                'propulsion-and-flight-vehicles': ['plane.html'],
                avionics: ['plane.html','dsta.html'],
                'electrical-systems-and-instrumentation': ['plane.html','ytu.html','mtu.html'],
                'fuel-and-propellant-engineering': ['plane.html'],
                'space-systems-engineering': ['plane.html'],
                'mechatronic-engineering': ['ytu.html','mtu.html','nspu.html','plane.html','dsta.html'],
                'chemical-engineering': ['ytu.html','mtu.html','nspu.html'],
                'food-engineering': ['ytu.html','mtu.html','nspu.html','tuhmawbi.html','tupathein.html','tuhinthada.html'],
                'textile-engineering': ['ytu.html','mtu.html','nspu.html','tuhmawbi.html','tumeiktila.html'],
                'mining-engineering': ['ytu.html','mtu.html','nspu.html','tumonywa.html','tusagaing.html'],
                'petroleum-engineering': ['ytu.html','mtu.html','nspu.html'],
                'metallurgical-engineering-and-materials-science': ['ytu.html','mtu.html','nspu.html']
            },
            medicine: {
                'general-practitioner-gp': ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                'general-surgeon': ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                pediatrician: ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                anesthesiologist: ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                cardiologist: ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                'dental-surgeon-dentist': ['dentalyangon.html','dentalmandalay.html'],
                pharmacist: ['pharmacyyangon.html','pharmacymandalay.html'],
                radiologist: ['medtechyangon.html','medtechmandalay.html'],
                'medical-laboratory-scientist': ['medtechyangon.html','medtechmandalay.html'],
                'registered-nurse-rn': ['nursingyangon.html','nursingmandalay.html'],
                'obstetrician-and-gynecologist': ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                'public-health-specialist': ['healthmagway.html','um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html'],
                physiotherapist: ['medtechyangon.html','medtechmandalay.html'],
                'clinical-research-scientist': ['um1.html','um2.html','umm.html','ummagway.html','umtaunggyi.html','medtechyangon.html','medtechmandalay.html'],
                'biomedical-engineer': ['medtechyangon.html','medtechmandalay.html','ytu.html','mtu.html']
            },
            business: {
                'business-analyst': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'business-manager': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'marketing-specialist': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'financial-analyst': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'banking-officer': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'human-resources-specialist': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                accountant: ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                entrepreneur: ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'management-consultant': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'international-business-specialist': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'sales-manager': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html'],
                'market-research-analyst': ['yueco.html','meikeco.html','monywaeco.html','cothanlyin.html','cosagaing.html','yeco.html']
            },
            agriculture: {
                'agronomist-and-crop-scientist': ['yau.html'],
                'veterinarian-and-animal-specialist': ['uvs.html'],
                'forestry-officer': ['ufes.html'],
                'environmental-consultant': ['ufes.html']
            },
            arts: {
                'fine-artist-and-illustrator': ['nuac-ygn.html','nuac-mdy.html','yu.html','mu.html','nsa.html'],
                'cultural-heritage-specialist': ['nuac-ygn.html','nuac-mdy.html','yu.html','mu.html','nsa.html'],
                'multimedia-designer': ['nuac-ygn.html','nuac-mdy.html','nsa.html'],
                'performing-arts-director': ['nuac-ygn.html','nuac-mdy.html']
            },
            'arts-science': {
                'research-scientist': ['yu.html','mu.html','dagon.html','eastyangon.html','wyu.html','yadanabon.html','mawlamyineuni.html','taunggyiuni.html','sittweuni.html','patheinuni.html','pyayuni.html','myitkyinauni.html','magwayuni.html','hinthadauni.html','hpaanuni.html','kalayuni.html','kyaingtonguni.html','kyaukseuni.html','lashouni.html','loikawuni.html','maubinuni.html','meiktilauni.html','monywauni.html','myeikuni.html','pakokkuuni.html','panglonguni.html','sagainguni.html','shwebo uni.html','taungoo uni.html','yenaunguni.html','taunggokeuni.html','bagouni.html','bhamouni.html','daweiuni.html','yude.html','mudoe.html'],
                'data-analyst': ['yu.html','mu.html','dagon.html','eastyangon.html','wyu.html','yadanabon.html','mawlamyineuni.html','taunggyiuni.html','sittweuni.html','patheinuni.html','pyayuni.html','myitkyinauni.html','magwayuni.html','hinthadauni.html','hpaanuni.html','kalayuni.html','kyaingtonguni.html','kyaukseuni.html','lashouni.html','loikawuni.html','maubinuni.html','meiktilauni.html','monywauni.html','myeikuni.html','pakokkuuni.html','panglonguni.html','sagainguni.html','shwebouni.html','taungoouni.html','yenaunguni.html','taunggokeuni.html','bagouni.html','bhamouni.html','daweiuni.html','yude.html','mudoe.html'],
                'public-relations-specialist': ['yu.html','mu.html','dagon.html','eastyangon.html','wyu.html','yadanabon.html','mawlamyineuni.html','taunggyiuni.html','sittweuni.html','patheinuni.html','pyayuni.html','myitkyinauni.html','magwayuni.html','hinthadauni.html','hpaanuni.html','kalayuni.html','kyaingtonguni.html','kyaukseuni.html','lashouni.html','loikawuni.html','maubinuni.html','meiktilauni.html','monywauni.html','myeikuni.html','pakokkuuni.html','panglonguni.html','sagainguni.html','shwebouni.html','taungoouni.html','yenaunguni.html','taunggokeuni.html','bagouni.html','bhamouni.html','daweiuni.html','yude.html','mudoe.html'],
                'human-resources-manager': ['yu.html','mu.html','dagon.html','eastyangon.html','wyu.html','yadanabon.html','mawlamyineuni.html','taunggyiuni.html','sittweuni.html','patheinuni.html','pyayuni.html','myitkyinauni.html','magwayuni.html','hinthadauni.html','hpaanuni.html','kalayuni.html','kyaingtonguni.html','kyaukseuni.html','lashouni.html','loikawuni.html','maubinuni.html','meiktilauni.html','monywauni.html','myeikuni.html','pakokkuuni.html','panglonguni.html','sagainguni.html','shwebouni.html','taungoouni.html','yenaunguni.html','taunggokeuni.html','bagouni.html','bhamouni.html','daweiuni.html','yude.html','mudoe.html']
            },
            language: {
                'professional-translator-and-interpreter': ['yufl.html','mufl.html'],
                'foreign-affairs-and-diplomatic-officer': ['yufl.html','mufl.html'],
                'international-business-coordinator': ['yufl.html','mufl.html'],
                'language-educator-and-specialist': ['yufl.html','mufl.html']
            },
            education: {
                'primary-and-secondary-educator': ['yuoe.html','suoe.html','nsa.html'],
                'curriculum-developer': ['yuoe.html','suoe.html','nsa.html'],
                'educational-administrator': ['yuoe.html','suoe.html','nsa.html'],
                'special-education-teacher': ['yuoe.html','suoe.html','nsa.html']
            },
            defense: {
                'commissioned-officer': ['dsa.html','dsta.html','dsma.html'],
                'military-engineer': ['dsta.html','dsa.html'],
                'military-medical-officer': ['dsma.html','dsa.html'],
                'intelligence-analyst': ['dsa.html','dsta.html']
            }
        };

        function categoryKey() {
            const t = normalizeText(document.title).toLowerCase();
            if (t.includes('engineering')) return 'engineering';
            if (t.includes('computer science') || t.includes('computer & it')) return 'computerscience';
            if (t.includes('medical') || t.includes('medicine') || t.includes('health')) return 'medicine';
            if (t.includes('business')) return 'business';
            if (t.includes('arts & science')) return 'arts-science';
            if (t.includes('arts & culture')) return 'arts';
            if (t.includes('agriculture')) return 'agriculture';
            if (t.includes('language')) return 'language';
            if (t.includes('education')) return 'education';
            if (t.includes('defense') || t.includes('military')) return 'defense';
            if (t.includes('law') || t.includes('legal')) return 'law';
            return 'generic';
        }
        const titleCategory = categoryKey();
        const explicitCategorySlug = (document.body.getAttribute('data-yu-opportunity-slug') || '').trim().toLowerCase();
        const categoryKeyBySlug = {
            engineering: 'engineering', 'computer-science': 'computerscience', medicine: 'medicine',
            business: 'business', 'arts-science': 'arts-science', 'arts-culture': 'arts',
            agriculture: 'agriculture', 'foreign-languages': 'language', education: 'education',
            defense: 'defense', law: 'law'
        };
        const categorySlugs = {
            engineering: 'engineering', computerscience: 'computer-science', medicine: 'medicine',
            business: 'business', 'arts-science': 'arts-science', arts: 'arts-culture',
            agriculture: 'agriculture', language: 'foreign-languages', education: 'education',
            defense: 'defense', law: 'law'
        };
        const category = categoryKeyBySlug[explicitCategorySlug] || titleCategory;
        const pageRules = known[category] || {};
        const categorySlug = explicitCategorySlug || categorySlugs[category] || '';
        let managedItems = [];
        let managedFetchSucceeded = false;
        if (categorySlug) {
            try {
                const response = await fetch('specializations_api.php?category=' + encodeURIComponent(categorySlug), {
                    credentials: 'same-origin', headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const payload = await response.json();
                    if (Array.isArray(payload.specializations)) {
                        managedItems = payload.specializations;
                        managedFetchSucceeded = true;
                    }
                }
            } catch (_) { /* Existing static career cards remain usable if the database API is offline. */ }
        }
        const bodyMode = document.body.getAttribute('data-yu-specializations-mode') || '';
        if (managedFetchSucceeded && (managedItems.length > 0 || bodyMode === 'replace')) {
            const esc = value => String(value == null ? '' : value).replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
            const cardHtml = item => '<div class="job-card yu-managed-specialization" data-program-key="' + esc(item.slug) + '">' +
                '<div class="job-header"><div class="job-icon">' + esc(item.icon || '🎓') + '</div><span class="badge">' + esc(item.badge_text || 'Major / Specialization') + '</span></div>' +
                '<h3 data-yu-en="' + esc(item.title) + '" data-yu-my="' + esc(item.title_my || '') + '">' + esc(item.title) + '</h3>' +
                '<div class="job-detail"><strong data-i18n="specializations.studyLabel">What you will study</strong><p data-yu-en="' + esc(item.description) + '" data-yu-my="' + esc(item.description_my || '') + '">' + esc(item.description) + '</p></div>' +
                (item.work_context ? '<div class="job-detail"><strong data-i18n="specializations.contextLabel">Related pathways / context</strong><p data-yu-en="' + esc(item.work_context) + '" data-yu-my="' + esc(item.work_context_my || '') + '">' + esc(item.work_context) + '</p></div>' : '') +
                '</div>';
            const existingGrid = document.querySelector('.jobs-grid');
            if (bodyMode === 'replace' && existingGrid) {
                existingGrid.innerHTML = managedItems.length ? managedItems.map(cardHtml).join('') : '<p class="yu-managed-intro yu-managed-empty" data-i18n="specializations.empty">No specializations are published for this category yet.</p>';
                const careerHeading = Array.from(document.querySelectorAll('.section-title')).find(el => /explore career paths|majors & specializations/i.test(el.textContent));
                if (careerHeading) { careerHeading.textContent = 'Majors & Specializations'; careerHeading.setAttribute('data-i18n','specializations.heading'); }
            } else if (existingGrid && managedItems.length) {
                const careerHeading = Array.from(document.querySelectorAll('.section-title')).find(el => /explore career paths/i.test(el.textContent));
                const section = document.createElement('section');
                section.className = 'yu-managed-specializations-section';
                section.innerHTML = '<h2 class="section-title" data-i18n="specializations.heading">Majors &amp; Specializations</h2><p class="yu-managed-intro" data-i18n="specializations.intro">Study areas published by the YourUni administration. University associations are labelled according to their verification status.</p><div class="jobs-grid yu-managed-specializations-grid">' + managedItems.map(cardHtml).join('') + '</div>';
                if (careerHeading && careerHeading.parentNode) careerHeading.parentNode.insertBefore(section, careerHeading);
                else existingGrid.parentNode.insertBefore(section, existingGrid);
            }
            if (typeof applyLanguage === 'function') applyLanguage(currentLanguage);
        }
        const jobs = Array.from(document.querySelectorAll('.job-card'));
        if (!jobs.length) return;
        const tools = document.createElement('div');
        tools.className = 'yu-specialty-tools';
        tools.innerHTML = '<div><span class="yu-selected-name" id="yu-selected-program">Select a career card</span><p data-i18n="Click a career card to highlight universities offering or related to that program. Unconfirmed entries are not highlighted; verify the official program list before applying.">Click a career card to highlight universities offering or related to that program. Unconfirmed entries are not highlighted; verify the official program list before applying.</p></div><button type="button" id="yu-show-all">Show all universities</button>';
        list.parentNode.insertBefore(tools, list);
        const status = document.createElement('p');
        status.className = 'yu-program-status';
        status.setAttribute('aria-live', 'polite');
        list.parentNode.insertBefore(status, list.nextSibling);

        function clearFilter() {
            // Restore the original university order when the user clears a specialty filter.
            originalUniversityOrder.forEach(a => list.appendChild(a));
            universityLinks.forEach(a => { a.classList.remove('yu-program-match','yu-program-dim','yu-program-unverified'); a.removeAttribute('aria-description'); });
            jobs.forEach(j => j.setAttribute('aria-pressed', 'false'));
            document.getElementById('yu-selected-program').textContent = 'Select a career card';
            status.textContent = '';
        }
        document.getElementById('yu-show-all').addEventListener('click', clearFilter);
        jobs.forEach(job => {
            const heading = job.querySelector('h3,h2,h4');
            if (!heading) return;
            const key = job.getAttribute('data-program-key') || programSlug(heading.textContent);
            job.classList.add('yu-program-clickable');
            job.setAttribute('tabindex', '0');
            job.setAttribute('role', 'button');
            job.setAttribute('aria-pressed', 'false');
            job.setAttribute('aria-label', 'Filter universities by ' + heading.textContent.trim());
            async function apply() {
                clearFilter();
                let allowed;
                let databaseMappings = [];
                let usingCategoryMapping = false;
                try {
                    const response = await fetch('programs_api.php?key=' + encodeURIComponent(key), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                    if (response.ok) {
                        const payload = await response.json();
                        if (Array.isArray(payload.mappings)) databaseMappings = payload.mappings;
                    }
                } catch (_) { /* Static starter mapping remains available if local API is offline. */ }
                if (databaseMappings.length === 0 && categorySlug && key !== categorySlug) {
                    try {
                        const response = await fetch('programs_api.php?key=' + encodeURIComponent(categorySlug), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        if (response.ok) {
                            const payload = await response.json();
                            if (Array.isArray(payload.mappings) && payload.mappings.length) {
                                databaseMappings = payload.mappings;
                                usingCategoryMapping = true;
                            }
                        }
                    } catch (_) { /* Keep existing career-specific starter mapping if category data is unavailable. */ }
                }
                const confirmedOffers = databaseMappings.filter(x => x.status === 'offered');
                const unverifiedCandidates = databaseMappings.filter(x => x.status === 'unverified');
                if (confirmedOffers.length > 0 || unverifiedCandidates.length > 0) {
                    // Positive database mappings take precedence over starter candidates.
                    allowed = confirmedOffers.concat(unverifiedCandidates).map(x => String(x.page || '').split(/[?#]/)[0].split('/').pop().toLowerCase());
                } else {
                    // Negative-only records are exclusions, not a complete list of positive matches.
                    // Keep the older starter candidates available as unverified options.
                    allowed = pageRules[key];
                }
                const explicitNotOffered = new Set(databaseMappings.filter(x => x.status === 'not_offered').map(x => String(x.page || '').split(/[?#]/)[0].split('/').pop().toLowerCase()));
                const unverifiedSet = new Set(unverifiedCandidates.map(x => String(x.page || '').split(/[?#]/)[0].split('/').pop().toLowerCase()));
                const usingLegacyStarterMapping = confirmedOffers.length === 0 && unverifiedCandidates.length === 0 && Array.isArray(allowed) && allowed.length > 0;
                if (usingLegacyStarterMapping) allowed.forEach(path => unverifiedSet.add(String(path).toLowerCase()));
                if (!allowed && databaseMappings.length === 0 && category === 'computerscience') {
                    // The source file lists career paths, not official university specializations.
                    // Show the CS directory as related options rather than inventing a narrow mapping.
                    universityLinks.forEach(a => a.classList.add('yu-program-unverified'));
                    document.getElementById('yu-selected-program').textContent = heading.textContent.trim();
                    status.textContent = universityLinks.length + ' related options are shown as unverified. Check each institution\'s current curriculum for this career path.';
                    jobs.forEach(j => j.setAttribute('aria-pressed', j === job ? 'true' : 'false'));
                    return;
                }
                if (!allowed && databaseMappings.length === 0 && (category === 'arts-science')) {
                    universityLinks.forEach(a => a.classList.add('yu-program-unverified'));
                    document.getElementById('yu-selected-program').textContent = heading.textContent.trim();
                    status.textContent = universityLinks.length + ' related options are shown as unverified. Check individual department offerings for this study or career path.';
                    jobs.forEach(j => j.setAttribute('aria-pressed', j === job ? 'true' : 'false'));
                    return;
                }
                if (!allowed) {
                    // A track without a verified mapping must not falsely hide every university.
                    document.getElementById('yu-selected-program').textContent = heading.textContent.trim();
                    status.textContent = 'No program-specific mapping is configured for this track yet; all universities remain visible. Verify official course lists.';
                    clearFilter();
                    document.getElementById('yu-selected-program').textContent = heading.textContent.trim();
                    status.textContent = 'No program-specific mapping is configured for this track yet; all universities remain visible. Verify official course lists.';
                    return;
                }
                const allowedSet = new Set(allowed.map(x => x.toLowerCase()));
                let shown = 0;
                let unverifiedShown = 0;
                // Confirmed offers come first, then unverified candidates, then dimmed options.
                const matchTier = a => {
                    const path = pathOf(a);
                    if (explicitNotOffered.has(path)) return 0;
                    if (!allowedSet.has(path)) return 0;
                    return unverifiedSet.has(path) ? 1 : 2;
                };
                const orderedLinks = originalUniversityOrder.slice().sort((a, b) => matchTier(b) - matchTier(a));
                orderedLinks.forEach(a => list.appendChild(a));
                universityLinks.forEach(a => {
                    const path = pathOf(a);
                    const isExcluded = explicitNotOffered.has(path);
                    const isCandidate = allowedSet.has(path) && !isExcluded;
                    const isUnverified = isCandidate && unverifiedSet.has(path);
                    a.classList.toggle('yu-program-match', isCandidate && !isUnverified);
                    a.classList.toggle('yu-program-unverified', isUnverified);
                    a.classList.toggle('yu-program-dim', !isCandidate);
                    if (isCandidate && !isUnverified) shown++;
                    if (isUnverified) unverifiedShown++;
                });
                jobs.forEach(j => j.setAttribute('aria-pressed', j === job ? 'true' : 'false'));
                document.getElementById('yu-selected-program').textContent = heading.textContent.trim();
                const negativeCount = universityLinks.filter(a => explicitNotOffered.has(pathOf(a))).length;
                if (usingCategoryMapping) {
                    const notListedCount = universityLinks.filter(a => explicitNotOffered.has(pathOf(a))).length;
                    status.textContent = 'Field-level evidence from YourUni major cards: ' + shown + ' listed match(es), ' + notListedCount + ' not listed, and ' + unverifiedShown + ' unverified. This is a broad field match, not confirmation of this specific career or an independent official-source check.';
                } else {
                    status.textContent = shown + ' confirmed match(es), ' + unverifiedShown + ' unverified candidate(s). ' + negativeCount + ' recorded non-offering record(s). Other cards are not highlighted; verify official program lists before applying.' + (usingLegacyStarterMapping ? ' These starter candidates are not verified against official sources.' : '');
                }
            }
            job.addEventListener('click', event => {
                if (event.target.closest('a,button,input,select,textarea')) return;
                if (job.getAttribute('aria-pressed') === 'true') clearFilter(); else apply();
            });
            job.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); apply(); }
            });
        });
    }

    function init() {
        createControls();
        applyTheme(safeGet('youruni-theme', 'system'));
        setupUniversityDescription();
        applyLanguage(safeGet('youruni-language-choice', 'my'));
        setupHomeNotice();
        setupSpecialtyFilter();
        // Translate controls inserted by the specialty-filter setup as well.
        applyLanguage(currentLanguage);
        if (window.matchMedia) {
            const media = window.matchMedia('(prefers-color-scheme: dark)');
            const listener = () => { if (safeGet('youruni-theme', 'system') === 'system') applyTheme('system'); };
            if (media.addEventListener) media.addEventListener('change', listener);
            else if (media.addListener) media.addListener(listener);
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
