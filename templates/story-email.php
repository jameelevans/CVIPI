<?php defined( 'ABSPATH' ) || exit; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo esc_html( $subject ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#edf1f2;color:<?php echo $brand['deep']; ?>;font-family:'Syne',Arial,sans-serif;letter-spacing:0;">
  <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;"><?php echo esc_html( $heading ); ?></div>
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#edf1f2;">
    <tr><td align="center" style="padding:24px 12px;">
      <!--[if mso]><table role="presentation" width="600" align="center"><tr><td><![endif]-->
      <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;background-color:<?php echo $brand['white']; ?>;">
        <tr><td bgcolor="<?php echo $brand['deep']; ?>" style="padding:28px 32px;border-bottom:6px solid <?php echo $brand['carmine']; ?>;">
          <img src="<?php echo esc_attr( $logo ); ?>" alt="CVIPI" width="160" height="84" style="display:block;width:160px;max-width:100%;height:auto;border:0;color:<?php echo $brand['white']; ?>;font-size:28px;">
        </td></tr>
        <tr><td style="padding:36px 32px 20px;overflow-wrap:anywhere;word-break:break-word;">
          <p style="margin:0 0 16px;font-family:'Syne',Arial,sans-serif;font-size:12px;line-height:18px;font-weight:bold;color:<?php echo $brand['blue']; ?>;"><?php echo esc_html( $eyebrow ); ?></p>
          <h1 style="margin:0 0 24px;font-family:'Fraunces',Georgia,serif;font-size:32px;line-height:39px;font-weight:normal;color:<?php echo $brand['deep']; ?>;"><?php echo esc_html( $heading ); ?></h1>
          <?php foreach ( preg_split( '/\n\s*\n/', $message ) as $paragraph ) : ?>
            <p style="margin:0 0 20px;font-size:16px;line-height:26px;color:<?php echo $brand['deep']; ?>;"><?php echo nl2br( esc_html( $paragraph ) ); ?></p>
          <?php endforeach; ?>
          <?php if ( $details ) : ?>
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #d8e1e4;margin-top:24px;">
              <?php foreach ( $details as $label => $value ) : if ( '' === $value ) continue; ?>
                <tr><td style="padding:12px 0;border-bottom:1px solid #d8e1e4;font-size:16px;line-height:24px;overflow-wrap:anywhere;word-break:break-word;"><strong style="color:<?php echo $brand['blue']; ?>;font-size:12px;"><?php echo esc_html( strtoupper( $label ) ); ?></strong><br><?php echo nl2br( esc_html( $value ) ); ?></td></tr>
              <?php endforeach; ?>
            </table>
          <?php endif; ?>
          <?php if ( $review_url ) : ?>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;"><tr><td bgcolor="<?php echo $brand['blue']; ?>" style="border-radius:4px;text-align:center;mso-padding-alt:16px 24px;"><a href="<?php echo esc_url( $review_url ); ?>" style="display:inline-block;padding:16px 24px;color:<?php echo $brand['white']; ?>;font-size:16px;line-height:22px;font-weight:bold;text-decoration:none;"><?php echo esc_html( $button_label ); ?></a></td></tr></table>
            <p style="margin:12px 0 0;font-size:12px;line-height:20px;color:<?php echo $brand['blue']; ?>;">WordPress sign-in required.</p>
          <?php endif; ?>
        </td></tr>
        <tr><td style="padding:24px 32px 32px;border-top:1px solid #d8e1e4;">
          <p style="margin:0 0 8px;font-family:'Fraunces',Georgia,serif;font-size:19px;line-height:26px;color:<?php echo $brand['blue']; ?>;">Community-led safety.</p>
          <p style="margin:0;font-size:12px;line-height:20px;color:<?php echo $brand['deep']; ?>;">Community Violence Intervention &amp; Prevention Initiative</p>
          <p style="margin:8px 0 0;font-size:12px;line-height:20px;"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:<?php echo $brand['blue']; ?>;text-decoration:underline;">Visit CVIPI</a></p>
        </td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td></tr>
  </table>
</body>
</html>
