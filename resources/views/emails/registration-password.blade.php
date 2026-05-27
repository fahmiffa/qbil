<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil</title>
</head>

<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f7f6; padding: 20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1a202c; padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 2px;">QBIL</h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #2d3748; margin-top: 0;">Halo, {{ $name }}!</h2>
                            <p style="color: #4a5568; line-height: 1.6; font-size: 16px;">
                                Selamat bergabung di <strong>QBIL</strong>. Pendaftaran Anda telah berhasil dilakukan dan akun Anda telah <strong>AKTIF</strong>.
                            </p>

                            <div style="background-color: #edf2f7; padding: 20px; border-radius: 6px; margin: 30px 0;">
                                <p style="margin: 0 0 10px 0; color: #718096; font-size: 14px; text-transform: uppercase; font-weight: bold;">Detail Akun Login:</p>
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td width="100" style="padding: 5px 0; color: #4a5568; font-weight: bold;">Email:</td>
                                        <td style="padding: 5px 0; color: #2d3748;">{{ $email }}</td>
                                    </tr>
                                    <tr>
                                        <td width="100" style="padding: 5px 0; color: #4a5568; font-weight: bold;">Password:</td>
                                        <td style="padding: 5px 0; color: #2d3748; font-family: monospace; font-size: 18px; letter-spacing: 1px;">{{ $password }}</td>
                                    </tr>
                                </table>
                            </div>

                            <p style="color: #4a5568; line-height: 1.6; font-size: 14px; margin-bottom: 30px;">
                                Harap simpan informasi ini dengan aman. Anda akan menerima notifikasi lanjutan setelah akun Anda diaktifkan oleh tim kami.
                            </p>

                            <!-- Button -->
                            <div style="text-align: center;">
                                <a href="{{ config('app.url') }}/login" style="background-color: #3182ce; color: #ffffff; padding: 15px 35px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                                    Login ke Dashboard
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 30px; background-color: #f7fafc; text-align: center; border-top: 1px solid #edf2f7;">
                            <p style="color: #a0aec0; font-size: 12px; margin: 0;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                                Tim Support {{ config('app.name') }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>