<!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>NSTP Registration Form</title>
<style>
@page { size: 8.5in 13in; margin: 12mm 13mm 12mm 13mm; }
* { box-sizing: border-box; }
body { font-family: Arial, DejaVu Sans, sans-serif; color: #101010; font-size: 8.5px; line-height: 1.18; }
.page { page-break-after: always; } .page:last-child { page-break-after: auto; }
.center { text-align:center; } .right { text-align:right; } .bold { font-weight: 700; }
.hdr { width:100%; border-collapse:collapse; margin-top:1mm; }
.hdr td { vertical-align:middle; } .logo { width:68px; max-height:68px; object-fit:contain; }
.motto {font-size:8px;font-style:italic;font-weight:bold;text-align:center;margin-top:2px;}
.control {width:100%;border-collapse:collapse;font-size:8px;line-height:1.08;}
.control td {border:1px solid #111;height:15px;padding:1px 3px;}
.control-dark {background:#111;width:43%;}
.military-sub {font-size:9px;line-height:1.14;}
.military-place {font-size:9px;font-style:italic;margin-top:2px;}
.footer-signatory {margin-top:2px;font-size:9px;text-align:center;}
.university { font-size:12px; font-weight:800; line-height:1.12; }
.topline { font-size:9px; }
.form-title { text-align:center; font-weight:800; font-size:12px; margin: 7px 0 2px; }
.line { display:inline-block; border-bottom:1px solid #111; padding:0 3px 1px; min-height:13px; vertical-align:bottom; }
.check { font-size:12px; margin:0 2px; }
.row { margin:6px 0; width:100%; }
.row td { vertical-align:bottom; padding:3px 2px 5px 0; }
.fields { border-collapse:collapse; width:100%; } .fields td { padding: 3px 2px 4px 0; vertical-align:bottom; }
.underline { border-bottom:1px solid #111; padding:0 3px; height:14px; font-size:9px; }
.field-caption { font-size:8px; color:#222; }
.label { font-weight:700; }
.sig-table { width:100%; border-collapse:collapse; margin-top:24px; }
.sig-table td { text-align:center; width:50%; padding:3px 8px; vertical-align:bottom; }
.sigline { border-bottom:1px solid #111; height:43px; position:relative; text-align:center; vertical-align:bottom; }
.student-signature { width:190px; height:40px; display:block; margin:0 auto; }
.cut { border-top:1px solid #222; margin:32px -2px 21px; }
.stub { font-size:9px; }
.stub .form-title { margin:5px 0 2px; }
.military-header { text-align:center; font-size:9px; line-height:1.22; }
.military-header strong { font-size:11px; }
.military-title { font-size:11px; font-weight:800; text-align:center; text-decoration:underline; margin:13px 0 2px; }
.compact { width:100%; border-collapse:collapse; margin:5px 0; }
.compact td { padding:2px 3px 3px 0; vertical-align:bottom; }
.inline-value { border-bottom:1px solid #111; display:inline-block; min-height:12px; padding:0 3px; vertical-align:bottom; }
.ms-table { border-collapse:collapse; width:100%; margin-top:4px; text-align:center; }
.ms-table th { border-bottom:1px solid #111; font-size:9px; padding:3px; }
.ms-table td { height:18px; border-bottom:1px solid #aaa; padding:2px; }
.certification { margin-top:18px; text-align:center; font-size:15px; font-weight:700; text-decoration:underline; letter-spacing:3px; }
.cert-text { margin-top:18px; font-size:11px; line-height:2; }
.signature-caption { font-size:8px; margin-top:3px; }
.note { font-size:7.5px; color:#333; }

/* Keep the original long-bond paper form compact and printable. */
.rotc-page .compact { margin: 2px 0; }
.rotc-page .compact td { padding: 1px 3px 2px 0; }
.rotc-page .underline { height: 12px; }
.rotc-page .ms-table { margin-top: 2px; }
.rotc-page .ms-table td { height: 15px; padding: 1px; }
.rotc-page .sig-table { margin-top: 8px !important; }
.rotc-page .sigline { height: 34px; }
.rotc-page .student-signature { height: 31px; }
/* A single table row is kept together by DomPDF, preventing split certificates. */
.certification-block { width:100%; border-collapse:collapse; page-break-inside:avoid; }
.certification-block td { padding:0; page-break-inside:avoid; }
.certification-block .certification { margin-top:10px; }
.certification-block .cert-text { margin-top:11px; line-height:1.55; }
</style></head><body>
@php
    $v = fn($value) => filled($value) ? (string)$value : '';
    $box = fn($chosen, $expected) => strtoupper(trim((string)$chosen)) === strtoupper(trim((string)$expected)) ? '☑' : '☐';
    $displayDate = function ($value) { try { return $value ? \Carbon\Carbon::parse($value)->format('d F Y') : ''; } catch (\Throwable $e) { return ''; } };
    $uniName = strtoupper((string)($university->name ?? 'UNIVERSITY'));
    $uniAcronym = strtoupper(trim((string)($university->acronym ?? '')));
    $isSnsu = $uniAcronym === 'SNSU' || str_contains($uniName, 'SURIGAO DEL NORTE STATE UNIVERSITY');
    $campusName = $isSnsu ? 'SURIGAO DEL NORTE STATE UNIVERSITY (SNSU) MAIN CAMPUS' : $uniName . ' MAIN CAMPUS';
    $generalAddress = $isSnsu ? 'Narciso Street, Surigao City' : $v($university->address ?? '');
    $rotcUnit = $isSnsu ? '1503RD (MASALIGAN) CDC, 15TH RCDG, RESCOM, PA' : '';
    $rotcAddress = $isSnsu ? 'Narciso St., Surigao City' : $v($university->address ?? '');
    $logo = $branding['university_logo'] ?? null;
    $rotcLogo = $branding['rotc_logo'] ?? null;
    $subject = strtoupper(trim((string)($student->subject ?? '')));
    $term = strtolower(trim((string)($student->term ?? '')));
    $gender = strtolower(trim((string)($student->gender ?? '')));
    $birth = $student->birth_date ?? $rotcProfile?->date_of_birth;
    $year = $university->academic_year ?? now()->format('Y').'-'.(now()->year+1);
    $studentId = $student->student_id_number ?? $rotcProfile?->nstp_id_no ?? '';
    $rotc = strtoupper(trim((string)$component)) === 'ROTC';
@endphp
<div class="page">
  <table class="hdr"><tr><td width="19%" class="center">@if($logo)<img class="logo" src="{{ $logo }}">@endif @if($isSnsu)<div class="motto">&ldquo;For Nation&#39;s Greater Heights!&rdquo;</div>@endif</td><td width="59%" style="padding-left:8px;"><div class="topline bold">Republic of the Philippines</div><div class="university">{{ $uniName }}</div><div class="topline bold">{{ $generalAddress }}</div></td><td width="22%"><table class="control"><tr><td rowspan="4" class="control-dark">&nbsp;</td><td class="bold">F/SELECT-NSTP-01</td></tr><tr><td>18</td></tr><tr><td>{{ now()->format('d F Y') }}</td></tr><tr><td>1 of 1</td></tr></table></td></tr></table>
  <div class="center" style="margin-top:4px; font-size:9px;">Office copy</div>
  <div class="form-title">NSTP REGISTRATION FORM</div>
  <div class="center">DATE: <span class="line" style="width:160px;">{{ $displayDate($student->created_at ?? null) }}</span></div>
  <table class="fields" style="margin-top:10px;"><tr><td width="51%"><span class="label">SUBJECT:</span> NSTP 1 <span class="check">{{ $box($subject,'NSTP 1') }}</span> &nbsp;&nbsp; NSTP 2 <span class="check">{{ $box($subject,'NSTP 2') }}</span></td><td><span class="label">COMPONENT:</span> CWTS <span class="check">{{ $box($component,'CWTS') }}</span> &nbsp; LTS <span class="check">{{ $box($component,'LTS') }}</span> &nbsp; ROTC <span class="check">{{ $box($component,'ROTC') }}</span></td></tr>
  <tr><td colspan="2"><span class="label">TERM:</span> &nbsp; FIRST SEM <span class="check">{{ str_contains($term,'1') || str_contains($term,'first') ? '☑' : '☐' }}</span> &nbsp; SECOND SEM <span class="check">{{ str_contains($term,'2') || str_contains($term,'second') ? '☑' : '☐' }}</span> &nbsp; SUMMER <span class="check">{{ str_contains($term,'summer') ? '☑' : '☐' }}</span></td></tr></table>
  <table class="fields"><tr><td width="36%"><span class="label">SURNAME:</span><div class="underline">{{ $v($student->surname) }}</div></td><td width="37%"><span class="label">FIRST NAME:</span><div class="underline">{{ $v($student->first_name) }}</div></td><td><span class="label">MIDDLE:</span><div class="underline">{{ $v($student->middle_name) }}</div></td></tr>
  <tr><td colspan="2"><span class="label">COURSE:</span><div class="underline">{{ $v($student->course) }}</div></td><td><span class="label">YEAR:</span> {{ $v($student->year_level) }} &nbsp;&nbsp; <span class="label">SECTION:</span> {{ $v($student->section) }}<div class="underline"></div></td></tr>
  <tr><td colspan="2"><span class="label">GENDER:</span> MALE {{ $gender === 'male' ? '☑' : '☐' }} &nbsp;&nbsp; FEMALE {{ $gender === 'female' ? '☑' : '☐' }}</td><td><span class="label">BIRTHDAY:</span><div class="underline">{{ $displayDate($birth) }}</div></td></tr>
  <tr><td colspan="2"><span class="label">CITY ADDRESS:</span><div class="underline">{{ $v($student->city_address) }}</div></td><td><span class="label">MUNICIPALITY:</span><div class="underline">{{ $v($student->municipality) }}</div></td></tr>
  <tr><td colspan="3"><span class="label">PROVINCE:</span><div class="underline">{{ $v($student->province) }}</div></td></tr>
  <tr><td colspan="3"><span class="label">E-MAIL ADD:</span><div class="underline">{{ $v($student->email) }}</div></td></tr>
  <tr><td colspan="3"><span class="label">CELL NO.:</span><div class="underline">{{ $v($student->contact_number) }}</div></td></tr></table>
  <div style="margin-top:7px; font-weight:bold;">PERSON TO BE NOTIFIED IN CASE OF EMERGENCY:</div>
  <table class="fields"><tr><td><span class="label">PARENTS / GUARDIAN:</span><div class="underline">{{ $v($student->guardian_name) }}</div></td></tr>
  <tr><td><span class="label">ADDRESS:</span><div class="underline">{{ $v($student->guardian_address) }}</div></td></tr>
  <tr><td><span class="label">CELL NO. / TELEPHONE NO.:</span><div class="underline">{{ $v($student->guardian_contact_number) }}</div></td></tr></table>
  <table class="sig-table"><tr><td><div class="sigline">@if(!empty($studentSignature))<img class="student-signature" src="{{ $studentSignature }}" alt="Student signature">@endif</div><div class="signature-caption">(Signature of Student)</div></td><td><div class="sigline"></div><div class="signature-caption">NSTP Director / Authorized Signatory</div></td></tr></table>
  <div class="cut"></div>
  <div class="stub"><div class="form-title">NSTP REGISTRATION FORM</div><div class="center">DATE: {{ $displayDate($student->created_at ?? null) }}</div>
  <table class="fields" style="margin-top:10px;"><tr><td><span class="label">SUBJECT:</span> NSTP 1 {{ $box($subject,'NSTP 1') }} &nbsp; NSTP 2 {{ $box($subject,'NSTP 2') }} &nbsp;&nbsp; <span class="label">COMPONENT:</span> CWTS {{ $box($component,'CWTS') }} &nbsp; LTS {{ $box($component,'LTS') }} &nbsp; ROTC {{ $box($component,'ROTC') }}<br><span class="label">TERM:</span> FIRST SEM {{ str_contains($term,'1') || str_contains($term,'first') ? '☑' : '☐' }} &nbsp; SECOND SEM {{ str_contains($term,'2') || str_contains($term,'second') ? '☑' : '☐' }} &nbsp; SUMMER {{ str_contains($term,'summer') ? '☑' : '☐' }}</td></tr>
  <tr><td><span class="label">SURNAME:</span> <span class="inline-value" style="width:170px;">{{ $v($student->surname) }}</span> &nbsp; <span class="label">FIRST NAME:</span> <span class="inline-value" style="width:165px;">{{ $v($student->first_name) }}</span> &nbsp; <span class="label">MIDDLE:</span> <span class="inline-value" style="width:110px;">{{ $v($student->middle_name) }}</span></td></tr></table>
  <table class="sig-table" style="margin-top:13px;"><tr><td><div class="sigline">@if(!empty($studentSignature))<img class="student-signature" src="{{ $studentSignature }}" alt="Student signature">@endif</div><div class="signature-caption">(Signature of Student)</div></td><td><div class="sigline"></div><div class="signature-caption">NSTP Director / Authorized Signatory</div></td></tr></table></div>
</div>
@if($rotc)
<div class="page rotc-page">
  <table class="hdr"><tr><td width="14%" class="center">@if(!empty($rotcLogo))<img class="logo" src="{{ $rotcLogo }}">@endif</td><td width="72%" class="center"><div class="military-header" style="letter-spacing:3px;">HEADQUARTERS</div><div class="military-header"><strong>DEPARTMENT OF MILITARY SCIENCE AND TACTICS</strong><br><strong>{{ $campusName }}</strong></div>@if($rotcUnit)<div class="military-sub">{{ $rotcUnit }}</div>@endif<div class="military-place">{{ $rotcAddress }}</div></td><td width="14%" class="center">@if($logo)<img class="logo" src="{{ $logo }}">@endif</td></tr></table>
  <div class="military-title">ROTC REGISTRATION FORM</div><div class="center">(Print all Entries)</div>
  <table class="compact" style="margin-top:12px"><tr><td>Student No.: <span class="inline-value" style="width:155px;">{{ $v($studentId) }}</span></td><td>MS: <span class="inline-value" style="width:55px;">{{ $v($rotcProfile?->ms_level ?? 'MS 1') }}</span></td><td>Date: <span class="inline-value" style="width:95px;">{{ $displayDate($rotcProfile?->submitted_at ?? null) }}</span></td></tr></table>
  <table class="compact"><tr><td width="33%">Name (Last):<div class="underline">{{ $v($rotcProfile?->last_name ?? $student->surname) }}</div></td><td width="33%">(First Name):<div class="underline">{{ $v($rotcProfile?->first_name ?? $student->first_name) }}</div></td><td>(Middle Name):<div class="underline">{{ $v($rotcProfile?->middle_name ?? $student->middle_name) }}</div></td></tr></table>
  <table class="compact"><tr><td width="24%">Gender:<div class="underline">{{ $v($rotcProfile?->gender ?? $student->gender) }}</div></td><td width="35%">Cellphone No.:<div class="underline">{{ $v($rotcProfile?->cellphone_number ?? $student->contact_number) }}</div></td><td>Email Add.:<div class="underline">{{ $v($rotcProfile?->contact_email ?? $student->email) }}</div></td></tr></table>
  <div class="label">Temporary Address:</div><table class="compact"><tr><td>No./St./Vill./Brgy.: <span class="inline-value" style="width:78%;">{{ $v($rotcProfile?->temporary_address_line ?? $student->city_address) }}</span></td></tr><tr><td>Municipality: <span class="inline-value" style="width:84%;">{{ $v($rotcProfile?->temporary_municipality ?? $student->municipality) }}</span></td></tr><tr><td>Province: <span class="inline-value" style="width:88%;">{{ $v($rotcProfile?->temporary_province ?? $student->province) }}</span></td></tr></table>
  <table class="compact"><tr><td width="32%">Course:<div class="underline">{{ $v($rotcProfile?->course ?? $student->course) }}</div></td><td width="35%">School:<div class="underline">{{ $v($rotcProfile?->school_name ?? $university->name) }}</div></td><td>Religion:<div class="underline">{{ $v($rotcProfile?->religion) }}</div></td></tr>
  <tr><td>Date of Birth:<div class="underline">{{ $displayDate($rotcProfile?->date_of_birth ?? $birth) }}</div></td><td>Place of Birth:<div class="underline">{{ $v($rotcProfile?->place_of_birth) }}</div></td><td>Blood Type:<div class="underline">{{ $v($rotcProfile?->blood_type) }}</div></td></tr>
  <tr><td>Height (cm):<div class="underline">{{ $v($rotcProfile?->height_cm) }}</div></td><td>Weight (kg):<div class="underline">{{ $v($rotcProfile?->weight_kg) }}</div></td><td>Complexion:<div class="underline">{{ $v($rotcProfile?->complexion) }}</div></td></tr></table>
  <div class="label">Permanent Address:</div><table class="compact"><tr><td>No./St./Vill./Brgy.: <span class="inline-value" style="width:78%;">{{ $v($rotcProfile?->permanent_address_line ?? $student->city_address) }}</span></td></tr><tr><td>Municipality: <span class="inline-value" style="width:84%;">{{ $v($rotcProfile?->permanent_municipality ?? $student->municipality) }}</span></td></tr><tr><td>Province: <span class="inline-value" style="width:88%;">{{ $v($rotcProfile?->permanent_province ?? $student->province) }}</span></td></tr></table>
  <table class="compact"><tr><td>Father:<div class="underline">{{ $v($rotcProfile?->father_name) }}</div></td><td>Occupation:<div class="underline">{{ $v($rotcProfile?->father_occupation) }}</div></td></tr><tr><td>Mother:<div class="underline">{{ $v($rotcProfile?->mother_name) }}</div></td><td>Occupation:<div class="underline">{{ $v($rotcProfile?->mother_occupation) }}</div></td></tr></table>
  <div class="label">Person to be notified in case of emergency:</div>
  <table class="compact"><tr><td>Name:<div class="underline">{{ $v($rotcProfile?->emergency_contact_name ?? $student->guardian_name) }}</div></td><td>Relationship:<div class="underline">{{ $v($rotcProfile?->emergency_contact_relationship) }}</div></td></tr><tr><td>Address:<div class="underline">{{ $v($rotcProfile?->emergency_contact_address ?? $student->guardian_address) }}</div></td><td>Tel No.:<div class="underline">{{ $v($rotcProfile?->emergency_contact_number ?? $student->guardian_contact_number) }}</div></td></tr></table>
  <div style="margin-top:10px;" class="label">Military Science Completed:</div>
  <table class="ms-table"><thead><tr><th>MS</th><th>SEMESTER</th><th>SCHOOL YEAR</th><th>GRADE</th><th>REMARKS</th></tr></thead><tbody>
  @for($i=0;$i<4;$i++) @php $r=$rotcProfile?->msRecords?->get($i); @endphp <tr><td>{{ $v($r?->ms_level) }}</td><td>{{ $v($r?->semester) }}</td><td>{{ $v($r?->school_year) }}</td><td>{{ $v($r?->grade) }}</td><td>{{ $v($r?->remarks) }}</td></tr> @endfor
  </tbody></table>
  <div style="margin-top:9px;">Are you willing to take the advance course? &nbsp;&nbsp; ({{ $rotcProfile?->willing_advance_course === true ? 'X' : ' ' }}) Yes &nbsp;&nbsp; ({{ $rotcProfile?->willing_advance_course === false ? 'X' : ' ' }}) No</div>
  <table class="sig-table" style="margin-top:13px;"><tr><td><div class="sigline">@if(!empty($studentSignature))<img class="student-signature" src="{{ $studentSignature }}" alt="Student signature">@endif</div><div class="signature-caption">(Signature of Student)</div></td><td><div class="sigline"></div><div class="signature-caption">ROTC Chief Clerk / Authorized Signatory</div></td></tr></table>
  <table class="certification-block"><tr><td>
  <div style="border-top:1px dashed #111; margin:9px 0 6px;"></div>
  <table class="hdr"><tr><td width="14%" class="center">@if(!empty($rotcLogo))<img class="logo" src="{{ $rotcLogo }}">@endif</td><td width="72%" class="center"><div class="military-header" style="letter-spacing:3px;">HEADQUARTERS</div><div class="military-header"><strong>DEPARTMENT OF MILITARY SCIENCE AND TACTICS</strong><br><strong>{{ $campusName }}</strong></div>@if($rotcUnit)<div class="military-sub">{{ $rotcUnit }}</div>@endif<div class="military-place">{{ $rotcAddress }}</div></td><td width="14%" class="center">@if($logo)<img class="logo" src="{{ $logo }}">@endif</td></tr></table>
  <div class="right" style="margin:12px 3px 0;">Date: ___________________</div>
  <div class="certification">CERTIFICATION</div>
  <div style="font-weight:bold; margin-top:9px;">TO WHOM IT MAY CONCERN:</div>
  <div class="cert-text">THIS IS TO CERTIFY that <strong>{{ $v($student->full_name) }}</strong>, a student of <strong>{{ $uniName }}</strong>, is duly registered in the ROTC program for the <strong>{{ $v($university->semester ?? $student->term) }}</strong>, School Year <strong>{{ $v($year) }}</strong>.</div>
  <div class="right" style="margin-top:10px;"><span class="inline-value" style="width:180px;"></span><br>ROTC Chief Clerk / Authorized Signatory</div>
  </td></tr></table>
</div>
@endif
</body></html>