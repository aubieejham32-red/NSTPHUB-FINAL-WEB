<style>
    @page {
        margin:
            108px
            34px
            66px
            34px;
    }

    * {
        box-sizing:
            border-box;
    }

    body {
        margin:
            0;

        font-family:
            DejaVu Sans,
            Arial,
            sans-serif;

        color:
            #111827;

        font-size:
            9px;

        line-height:
            1.35;
    }

    .pdf-header {
        position:
            fixed;

        top:
            -92px;

        left:
            0;

        right:
            0;

        height:
            82px;

        border-bottom:
            1.5px solid
            #1f2937;

        padding-bottom:
            8px;
    }

    .header-table,
    .footer-table,
    .meta-table,
    .data-table,
    .profile-table,
    .summary-table {
        width:
            100%;

        border-collapse:
            collapse;
    }

    .header-logo-cell {
        width:
            86px;

        text-align:
            center;

        vertical-align:
            middle;
    }

    .header-logo {
        max-width:
            62px;

        max-height:
            62px;
    }

    .header-center {
        text-align:
            center;

        vertical-align:
            middle;

        padding:
            0 10px;
    }

    .header-center .program {
        font-size:
            11px;

        font-weight:
            700;

        text-transform:
            uppercase;

        letter-spacing:
            .25px;
    }

    .header-center .university {
        margin-top:
            4px;

        font-size:
            10px;

        font-weight:
            700;
    }

    .header-center .location {
        margin-top:
            2px;

        font-size:
            8.5px;
    }

    /*
    |--------------------------------------------------------------------------
    | FOOTER
    |--------------------------------------------------------------------------
    */

    .pdf-footer {
        position:
            fixed;

        bottom:
            -52px;

        left:
            0;

        right:
            0;

        height:
            48px;

        border-top:
            1px solid
            #9ca3af;

        padding-top:
            7px;
    }

    .footer-cell {
        width:
            50%;

        vertical-align:
            top;

        text-align:
            center;

        padding:
            0 18px;
    }

    .signature-name {
        font-size:
            9px;

        font-weight:
            700;

        text-transform:
            uppercase;

        border-bottom:
            1px solid
            #111827;

        padding-bottom:
            2px;

        display:
            inline-block;

        min-width:
            210px;
    }

    .signature-role {
        margin-top:
            3px;

        font-size:
            7.5px;

        text-transform:
            uppercase;
    }

    .generated {
        position:
            absolute;

        right:
            0;

        bottom:
            0;

        font-size:
            6.5px;

        color:
            #6b7280;
    }

    /*
    |--------------------------------------------------------------------------
    | TITLES
    |--------------------------------------------------------------------------
    */

    .document-title {
        text-align:
            center;

        font-size:
            13px;

        font-weight:
            700;

        letter-spacing:
            .4px;

        margin:
            0 0 9px;

        text-transform:
            uppercase;
    }

    .document-subtitle {
        text-align:
            center;

        font-size:
            9px;

        margin:
            -5px 0 10px;

        color:
            #374151;
    }

    /*
    |--------------------------------------------------------------------------
    | META TABLE
    |--------------------------------------------------------------------------
    */

    .meta-table {
        margin-bottom:
            10px;
    }

    .meta-table td {
        border:
            1px solid
            #cbd5e1;

        padding:
            5px 7px;

        vertical-align:
            middle;
    }

    .meta-label {
        width:
            110px;

        background:
            #f3f4f6;

        font-weight:
            700;

        text-transform:
            uppercase;

        color:
            #374151;
    }

    /*
    |--------------------------------------------------------------------------
    | MAIN TABLE
    |--------------------------------------------------------------------------
    */

    .data-table {
        table-layout:
            fixed;
    }

    .data-table th {
        background:
            #233E47;

        color:
            #ffffff;

        border:
            1px solid
            #111827;

        padding:
            6px 4px;

        font-size:
            7.5px;

        font-weight:
            700;

        text-transform:
            uppercase;

        text-align:
            center;

        vertical-align:
            middle;
    }

    .data-table td {
        border:
            1px solid
            #9ca3af;

        padding:
            5px 4px;

        vertical-align:
            middle;

        word-wrap:
            break-word;
    }

    .data-table tbody
    tr:nth-child(even)
    td {
        background:
            #f8fafc;
    }

    .center {
        text-align:
            center;
    }

    .right {
        text-align:
            right;
    }

    .strong {
        font-weight:
            700;
    }

    .muted {
        color:
            #6b7280;
    }

    /*
    |--------------------------------------------------------------------------
    | SUMMARY
    |--------------------------------------------------------------------------
    */

    .summary-table {
        margin:
            8px 0 10px;

        table-layout:
            fixed;
    }

    .summary-table td {
        border:
            1px solid
            #cbd5e1;

        padding:
            6px;

        text-align:
            center;
    }

    .summary-number {
        display:
            block;

        font-size:
            13px;

        font-weight:
            700;

        color:
            #233E47;
    }

    .summary-label {
        display:
            block;

        font-size:
            7px;

        text-transform:
            uppercase;

        color:
            #4b5563;

        margin-top:
            2px;
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    .section-title {
        margin:
            11px 0 5px;

        padding-bottom:
            3px;

        border-bottom:
            1px solid
            #9ca3af;

        font-size:
            9.5px;

        font-weight:
            700;

        text-transform:
            uppercase;

        color:
            #233E47;
    }

    .profile-table {
        table-layout:
            fixed;
    }

    .profile-table td {
        border:
            1px solid
            #cbd5e1;

        padding:
            5px 7px;

        vertical-align:
            top;
    }

    .profile-label {
        width:
            18%;

        background:
            #f3f4f6;

        font-weight:
            700;

        color:
            #374151;

        text-transform:
            uppercase;

        font-size:
            7.5px;
    }

    .profile-value {
        width:
            32%;
    }

    .note {
        margin-top:
            8px;

        padding:
            6px 8px;

        border:
            1px solid
            #d1d5db;

        background:
            #f9fafb;

        color:
            #4b5563;

        font-size:
            7.5px;
    }

    .no-data {
        text-align:
            center;

        padding:
            18px;

        color:
            #6b7280;

        border:
            1px solid
            #d1d5db;
    }
</style>