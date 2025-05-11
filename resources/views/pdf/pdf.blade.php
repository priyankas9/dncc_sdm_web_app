<!DOCTYPE html>
<html lang="en">

<head>
    <style>
    .qr_header {
        
        background-color: #fff;
    }

    .row {
        display: flex;
    }

    .col {
        flex: 1;
    }
    .infos {
        width: 130px;
        height: 130px;
        margin-top: 20px;
        border-radius: 5px;
        border-color: black;
        text-align: center;
    }

    .info img {
        height: 100px;
        width: 100px;
    }

    .subject {
        padding: 40px 30px 20px 30px;
        text-align: center;
    }

    .pdf_body {
        line-height: 2;
        text-align: justify;
    }

    .footers {
        line-height: 1;
        padding: 20px;
    }

    .page {

        float: center;
        padding: 1.5rem;
    }

   
    body {
	    font-family: 'Bangla', Arial, sans-serif !important;
	}
    </style>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice</title>
</head>
<body>
    <div class="page new-page">
        <table width="100%">
            <tr>
                <td width="20%"></td>
                <td width="60%" style="text-align: center;">
                    <p style="font-weight: bold; font-size: 20px; margin: 0;">Dhaka North City Corporation</p>
                    <p style="font-weight: bold; font-size: 15px; margin: 5px 0;">Waste Management Department</p>
                    <p style="font-size: 10px; line-height: 1.5rem; margin: 5px 0;">
                        Plot No-23-26, Road No-46, Gulshan-2, Dhaka-1212
                    </p>
                    <a style="font-size: 18px;" href="#">www.dncc.gov.bd</a>
                </td>
                <td width="20%">
                    <div class="qr_header">
                    </div>
                </td>
            </tr>
        </table>
        <table width="100%" style="padding-top: 20px;">
        <tr>
            <td width="50%" style="text-align: left; font-size: 20px;">
                <p>Memo No: 46.10.0000.045.99.435.21-{{$pdf_data->unique_ref}}</p>
            </td>
            <td width="50%" style="text-align: right; font-size: 20px;">
                <p>Date: {{$pdf_data->date}}</p>
            </td>
        </tr>
        </table>
        <div class="subject" style="font-size: 20px;">
            <label>
                <p style="text-align: center;">
                    <b>Subject:</b> {{$pdf_data->subject}}
                </p>
            </label>
        </div>
        <div class="pdf_body" style=" font-size: 20px; line-height:1.5rem">
            <p class="custom-font">
                {{$pdf_data->paragraph}}
            </p>
        </div>
        <!-- <table width="100%" style="font-size: 20px;">
            <tr>
                <td width="60%">
                    <p>Owner/Resident</p>
                    <p>Holding No:</p>
                    <p>Road No:</p>
                    <p>Block No:</p>
                    <p>Ward No:</p>
                    <p>Area:</p>
                </td>
                <td width="40%" style="text-align: center">
                    <p style="font-weight: bold; font-size: 20px;">Signature</p>
                    <p style="font-weight: bold; font-size: 20px;">Name</p>
                    <p style="font-weight: bold; font-size: 20px;">Designation</p>
                    <p style="font-weight: bold; font-size: 20px;">Waste Management Department</p>
                    <p style="font-weight: bold; font-size: 20px;">Dhaka North City Corporation</p>
                </td>
            </tr>
        </table>
        <div style="font-size: 20px; line-height:1.5rem;">
            <p><b>Copy (Not Based on Seniority):</b></p>
            <ol style="font-size: 20px; line-height:1.5rem;">
                <li>For the kind information of the Hon'ble Mayor, through his private secretary, Dhaka North City Corporation;</li>
                <li>For the kind information of the Chief Executive Officer, through his staff officer, Dhaka North City Corporation;</li>
                <li>For the kind information of the Chief Waste Management Officer, through his personal assistant, Dhaka North City Corporation;</li>
                <li>Office Copy.</li>
            </ol>
        </div> -->
    </div>
   
    <script src="{{asset('js/app.js')}}"></script>
    <!-- Template Main JS File -->

</body>

</html>