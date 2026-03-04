<?php $pageTitle = 'Privacy Policy - Lush'; ?>

<style>
.legal-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 60px 24px 80px;
    color: #e0e0e0;
    line-height: 1.8;
}
.legal-content h1 {
    font-size: 2.2rem;
    margin-bottom: 8px;
    color: #fff;
}
.legal-content .last-updated {
    color: #888;
    font-size: 0.95rem;
    margin-bottom: 40px;
}
.legal-content h2 {
    font-size: 1.35rem;
    color: #fff;
    margin-top: 40px;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid rgba(224, 64, 251, 0.2);
}
.legal-content h3 {
    font-size: 1.1rem;
    color: #e0e0e0;
    margin-top: 20px;
    margin-bottom: 8px;
}
.legal-content p,
.legal-content li {
    font-size: 0.97rem;
    color: #ccc;
    margin-bottom: 12px;
}
.legal-content ul {
    padding-left: 24px;
    margin-bottom: 16px;
}
.legal-content ul li {
    margin-bottom: 6px;
}
.legal-content a {
    color: #e040fb;
    text-decoration: underline;
}
.legal-content strong {
    color: #e0e0e0;
}
.legal-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 16px 0 24px;
}
.legal-content table th,
.legal-content table td {
    text-align: left;
    padding: 10px 14px;
    border: 1px solid rgba(255,255,255,0.08);
    font-size: 0.95rem;
    color: #ccc;
}
.legal-content table th {
    background: rgba(224, 64, 251, 0.08);
    color: #e0e0e0;
    font-weight: 600;
}
</style>

<div class="legal-content">
    <h1>Privacy Policy</h1>
    <p class="last-updated">Last updated: <?= date('F j, Y') ?></p>

    <p>At Lush, your privacy matters. This Privacy Policy explains what information we collect, how we use it, how we protect it, and your rights regarding your personal data. By using the Lush platform (the "Service"), you agree to the practices described in this policy.</p>

    <h2>1. Information We Collect</h2>

    <h3>1.1 Account Information</h3>
    <p>When you create a Lush account, we collect:</p>
    <ul>
        <li><strong>Username</strong> and <strong>display name</strong></li>
        <li><strong>Email address</strong></li>
        <li><strong>Password</strong> (stored using industry-standard hashing; we never store plaintext passwords)</li>
        <li><strong>Profile information</strong> you choose to provide, such as bio, location, interests, occupation, and relationship status</li>
        <li><strong>Avatar or profile image</strong> (if uploaded)</li>
    </ul>

    <h3>1.2 Chat Messages and Conversations</h3>
    <p>We store the messages you exchange with AI companions in order to:</p>
    <ul>
        <li>Provide conversation continuity and memory features</li>
        <li>Improve our AI models and the quality of companion responses</li>
        <li>Detect and prevent abuse of the platform</li>
    </ul>
    <p>Your conversations are private and are not shared with other users. Chat data may be used in anonymized and aggregated form to improve our AI systems.</p>

    <h3>1.3 Payment Data</h3>
    <p>When you make a purchase, payment information is collected and processed by our third-party payment providers (see Section 6). We may store:</p>
    <ul>
        <li>Transaction IDs and purchase history</li>
        <li>Subscription status and billing cycle dates</li>
        <li>Last four digits of your payment card (for display purposes only)</li>
    </ul>
    <p>We do <strong>not</strong> store your full credit card number, CVV, or bank account details on our servers.</p>

    <h3>1.4 Usage and Technical Data</h3>
    <p>We automatically collect certain information when you use the Service:</p>
    <ul>
        <li>IP address and approximate geographic location</li>
        <li>Browser type and version, operating system, and device type</li>
        <li>Pages visited, features used, and time spent on the Service</li>
        <li>Referral source and referring URLs</li>
        <li>Error logs and performance data</li>
    </ul>

    <h2>2. How We Use Your Data</h2>
    <p>Lush uses your information for the following purposes:</p>
    <ul>
        <li><strong>Provide the Service:</strong> Deliver AI companion conversations, maintain your account, and process payments</li>
        <li><strong>Personalization:</strong> Remember your preferences and conversation history to deliver a better experience</li>
        <li><strong>Improvement:</strong> Analyze usage patterns and feedback to improve our AI models, features, and overall platform</li>
        <li><strong>Communication:</strong> Send you service-related emails, such as account verification, billing confirmations, security alerts, and important updates</li>
        <li><strong>Safety and Security:</strong> Detect and prevent fraud, abuse, and violations of our Terms of Service</li>
        <li><strong>Legal Compliance:</strong> Comply with applicable laws, regulations, and legal processes</li>
    </ul>
    <p>We will <strong>never</strong> sell your personal data to third parties for advertising purposes.</p>

    <h2>3. Data Retention</h2>
    <p>We retain your data as follows:</p>
    <table>
        <tr>
            <th>Data Type</th>
            <th>Retention Period</th>
        </tr>
        <tr>
            <td>Account information</td>
            <td>Until you delete your account</td>
        </tr>
        <tr>
            <td>Chat messages</td>
            <td>Until you delete your account or individual conversations</td>
        </tr>
        <tr>
            <td>Payment records</td>
            <td>7 years (as required by tax and financial regulations)</td>
        </tr>
        <tr>
            <td>Usage and technical data</td>
            <td>Up to 24 months from collection</td>
        </tr>
        <tr>
            <td>Server logs</td>
            <td>90 days</td>
        </tr>
    </table>
    <p>When you delete your account, we will remove your personal data within <strong>30 days</strong>, except where retention is required by law.</p>

    <h2>4. Cookies and Tracking</h2>
    <p>Lush uses cookies and similar technologies to:</p>
    <ul>
        <li><strong>Essential cookies:</strong> Maintain your login session, store preferences, and provide CSRF protection</li>
        <li><strong>Analytics cookies:</strong> Understand how users interact with the Service so we can improve it</li>
        <li><strong>Functional cookies:</strong> Remember your settings and provide enhanced features</li>
    </ul>
    <p>We do <strong>not</strong> use third-party advertising or tracking cookies. You can manage cookie preferences through your browser settings. Note that disabling essential cookies may prevent you from using certain features of the Service.</p>

    <h2>5. Data Sharing</h2>
    <p>Lush does not sell your personal data. We may share limited information with third parties only in the following circumstances:</p>
    <ul>
        <li><strong>With your consent:</strong> When you explicitly authorize sharing</li>
        <li><strong>Service providers:</strong> Trusted third-party services that help us operate the platform (see Section 6)</li>
        <li><strong>Legal requirements:</strong> When required by law, subpoena, court order, or government request</li>
        <li><strong>Safety:</strong> To protect the safety, rights, or property of Lush, our users, or the public</li>
        <li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets, with appropriate confidentiality protections</li>
    </ul>

    <h2>6. Third-Party Services</h2>
    <p>Lush integrates with the following third-party services to provide the platform:</p>

    <h3>OpenAI</h3>
    <p>We use OpenAI's API to power AI companion conversations. Your chat messages are sent to OpenAI for processing. OpenAI's data usage policies apply to this processing. We encourage you to review <a href="https://openai.com/policies/privacy-policy" target="_blank" rel="noopener">OpenAI's Privacy Policy</a>.</p>

    <h3>Stripe</h3>
    <p>We use Stripe to process credit and debit card payments. Stripe collects and processes payment information in accordance with their <a href="https://stripe.com/privacy" target="_blank" rel="noopener">Privacy Policy</a>. Lush does not have access to your full card details.</p>

    <h3>PayPal</h3>
    <p>We offer PayPal as an alternative payment method. When you pay via PayPal, your transaction is subject to <a href="https://www.paypal.com/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener">PayPal's Privacy Policy</a>.</p>

    <h2>7. Data Security</h2>
    <p>We implement industry-standard security measures to protect your data, including:</p>
    <ul>
        <li>Encryption of data in transit (TLS/HTTPS) and at rest</li>
        <li>Secure password hashing using bcrypt</li>
        <li>Regular security audits and vulnerability assessments</li>
        <li>Access controls limiting employee access to personal data on a need-to-know basis</li>
        <li>CSRF protection and rate limiting on all API endpoints</li>
    </ul>
    <p>While we strive to protect your data, no method of transmission or storage is 100% secure. We cannot guarantee absolute security.</p>

    <h2>8. Your Rights</h2>
    <p>Depending on your jurisdiction, you may have the following rights regarding your personal data:</p>
    <ul>
        <li><strong>Access:</strong> Request a copy of the personal data we hold about you</li>
        <li><strong>Correction:</strong> Request correction of inaccurate or incomplete data</li>
        <li><strong>Deletion:</strong> Request deletion of your personal data (see Section 9)</li>
        <li><strong>Portability:</strong> Request your data in a structured, machine-readable format</li>
        <li><strong>Objection:</strong> Object to certain types of data processing</li>
        <li><strong>Restriction:</strong> Request that we restrict processing of your data under certain circumstances</li>
        <li><strong>Withdraw consent:</strong> Where processing is based on consent, you may withdraw it at any time</li>
    </ul>
    <p>To exercise any of these rights, please contact us at <strong>privacy@lush.com</strong>. We will respond to your request within 30 days.</p>

    <h2>9. Account and Data Deletion</h2>
    <p>You can delete your Lush account at any time through your <a href="<?= url('profile') ?>">profile settings</a> or by contacting our support team. When you delete your account:</p>
    <ul>
        <li>Your profile information, chat history, and companion data will be permanently deleted within 30 days</li>
        <li>Payment records may be retained for up to 7 years as required by financial regulations</li>
        <li>Anonymized and aggregated data that cannot be linked back to you may be retained for analytical purposes</li>
        <li>Active subscriptions will be canceled, and no further charges will be made</li>
    </ul>

    <h2>10. Children's Privacy</h2>
    <p>Lush is not intended for use by anyone under the age of 18. We do not knowingly collect personal information from individuals under 18. If we learn that we have collected personal data from a minor, we will take immediate steps to delete that information. If you believe a minor has provided us with personal data, please contact us at <strong>privacy@lush.com</strong>.</p>

    <h2>11. International Data Transfers</h2>
    <p>Your data may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. When we transfer data internationally, we implement appropriate safeguards to protect your information, including standard contractual clauses and other legally recognized transfer mechanisms.</p>

    <h2>12. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. We will notify you of material changes by posting the updated policy on the Service and updating the "Last updated" date. For significant changes, we may also send you an email notification. Your continued use of the Service after changes are posted constitutes your acceptance of the revised policy.</p>

    <h2>13. Contact Us</h2>
    <p>If you have questions, concerns, or requests regarding this Privacy Policy or your personal data, please contact us at:</p>
    <p>
        <strong>Email:</strong> privacy@lush.com<br>
        <strong>Support:</strong> support@lush.com
    </p>
</div>
