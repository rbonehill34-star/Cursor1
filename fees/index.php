<?php
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fees - RJ Accountancy Limited</title>
    <meta name="description" content="Transparent fixed fee pricing for all our accountancy services. Company accounts, self-assessment, VAT returns, and more with clear, upfront costs.">
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .fees-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .fees-table th, .fees-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        .fees-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .fees-table td {
            font-size: 0.95rem;
            color: #555;
        }
        .fees-table tr:hover {
            background: #f8f9fa;
        }
        .fees-table tr:last-child td {
            border-bottom: none;
        }
        .fee-highlight {
            font-weight: 600;
            color: #667eea;
        }
        .fees-section {
            background: white;
            margin: 30px 0;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .fees-section h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .note-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 10px 10px 0;
        }
        .note-box h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .note-box p {
            margin: 0 0 15px 0;
            color: #666;
            line-height: 1.6;
        }
        .note-box p:last-child {
            margin-bottom: 0;
        }
        .fees-section p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .fees-section p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/RJA-icon Blue.png" alt="RJ Accountancy Logo" style="height: 30px; margin-right: 10px;">
                <span>RJ Accountancy Limited</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../about" class="nav-link">About Us</a>
                </li>
                <li class="nav-item">
                    <a href="../fees" class="nav-link active">Fees</a>
                </li>
                <li class="nav-item">
                    <a href="../contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="../login" class="nav-link">Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="contact-section">
            <div class="container">
                <div class="contact-header">
                    <h1 class="contact-title">Fixed Fee Schedule</h1>
                    <p class="contact-subtitle">Clear costs</p>
                </div>

                <div class="note-box">
                    <h4>How much does an accountant cost?</h4>
                    <p>The cost of accountancy fees for Limited company accounts start at £300 for a low turnover company. For self assessment accountant fees we charge £179 for a basic return.</p>
                    <p>Our fixed fee schedule is based on the requirements of your business and your turnover. Separate fees are charged for accounts preparation, filing of tax returns, VAT and PAYE. You will only be charged for the services you need.</p>
                    <p><strong>Please note these are total annual fees.</strong></p>
                </div>

                <div class="fees-section">
                    <h3>Company Accounts and Corporation Tax Return Fees</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Turnover</th>
                                <th>Fee Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>£0k to £10,000</td><td class="fee-highlight">£300 + VAT</td></tr>
                            <tr><td>£10,000 to £20,000</td><td class="fee-highlight">£360 + VAT</td></tr>
                            <tr><td>£20,000 to £30,000</td><td class="fee-highlight">£420 + VAT</td></tr>
                            <tr><td>£30,000 to £40,000</td><td class="fee-highlight">£480 + VAT</td></tr>
                            <tr><td>£40,000 to £55,000</td><td class="fee-highlight">£540 + VAT</td></tr>
                            <tr><td>£55,000 to £70,000</td><td class="fee-highlight">£600 + VAT</td></tr>
                            <tr><td>£70,000 to £100,000</td><td class="fee-highlight">£660 + VAT</td></tr>
                            <tr><td>£100,000 to £130,000</td><td class="fee-highlight">£720 + VAT</td></tr>
                            <tr><td>£130,000 to £160,000</td><td class="fee-highlight">£780 + VAT</td></tr>
                            <tr><td>£160,000 to £200,000</td><td class="fee-highlight">£840 + VAT</td></tr>
                            <tr><td>£200,000 to £250,000</td><td class="fee-highlight">£900 + VAT</td></tr>
                            <tr><td>£250,000 to £300,000</td><td class="fee-highlight">£960 + VAT</td></tr>
                            <tr><td>£300,000 to £400,000</td><td class="fee-highlight">£1020 + VAT</td></tr>
                            <tr><td>£400,000 to £500,000</td><td class="fee-highlight">£1080 + VAT</td></tr>
                            <tr><td>Others on request</td><td class="fee-highlight">-</td></tr>
                        </tbody>
                    </table>
                    <p><strong>Includes:</strong> All preparing and filing the Company accounts with Companies House and the Corporation Tax return with HMRC. We will also look at options to improve tax efficiency. The turnover is the net number in the accounts so does not include VAT.</p>
                    <p><strong>Note:</strong> This does not include confirmation statement, this is a very straightforward process so we encourage clients to do their own but we can complete this for £50 plus VAT plus the filing fee.</p>
                    <p><strong>Additional Work:</strong> Occasionally a company may have a period over 1 year. Since HMRC only accept corporation tax returns for up to 12 months a second CT600 is required and we charge an additional £50 plus VAT for this additional work.</p>
                </div>

                <div class="fees-section">
                    <h3>Individual Self Assessment Tax Return Fees</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Fee Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Self assessment tax return</td><td class="fee-highlight">£179 + VAT</td></tr>
                        </tbody>
                    </table>
                    <p>For a basic tax return with PAYE income, dividend income, interest income or pensions. Separate additional fees are charged if business accounts are required or property rental income calculations.</p>
                </div>

                <div class="fees-section">
                    <h3>Sole Trader/Partnership Accounts (Self-Assessment)</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Turnover</th>
                                <th>Fee Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>£0k to £10,000</td><td class="fee-highlight">£50 + VAT</td></tr>
                            <tr><td>£10,000 to £20,000</td><td class="fee-highlight">£100 + VAT</td></tr>
                            <tr><td>£20,000 to £30,000</td><td class="fee-highlight">£150 + VAT</td></tr>
                            <tr><td>£30,000 to £40,000</td><td class="fee-highlight">£200 + VAT</td></tr>
                            <tr><td>£40,000 to £55,000</td><td class="fee-highlight">£250 + VAT</td></tr>
                            <tr><td>£55,000 to £70,000</td><td class="fee-highlight">£300 + VAT</td></tr>
                            <tr><td>£70,000 to £100,000</td><td class="fee-highlight">£350 + VAT</td></tr>
                            <tr><td>£100,000 to £130,000</td><td class="fee-highlight">£400 + VAT</td></tr>
                            <tr><td>£130,000 to £160,000</td><td class="fee-highlight">£450 + VAT</td></tr>
                            <tr><td>£160,000 to £200,000</td><td class="fee-highlight">£500 + VAT</td></tr>
                        </tbody>
                    </table>
                    <p>If you have a Sole Trader or a Partnership business you will need to prepare accounts in order to complete the self assessment tax return. Our fee schedule reflect the additional work involved as a business grows.</p>
                    <p><strong>Example:</strong> The total fees for a person with a small business with turnover between £30k and £40k would be the £200 + VAT accounts fee along with the £179 + VAT self assessment return fee. So a total of £379 + VAT for all year end filings.</p>
                </div>

                <div class="fees-section">
                    <h3>Partnerships</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Fee Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Partnership return</td><td class="fee-highlight">£150 + VAT</td></tr>
                        </tbody>
                    </table>
                    <p>For a partnership there is an additional tax return to prepare along with the individual partners tax returns. We charge a £150 fee a partnership returns along with the business accounting fee and the cost of individual self assessment returns as shown above.</p>
                    <p>This is in addition to the self-assessment tax return fees for the individual partners. HMRC requires that a separate partnership return is completed along with the self assessment tax return for each partner.</p>
                </div>

                <div class="fees-section">
                    <h3>VAT Returns</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Annual turnover</th>
                                <th>Fee rate per return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>£0 to 100,000</td><td class="fee-highlight">£120 + VAT</td></tr>
                            <tr><td>£100,000 to 200,000</td><td class="fee-highlight">£180 + VAT</td></tr>
                            <tr><td>£200,000 to 400,000</td><td class="fee-highlight">£240 + VAT</td></tr>
                        </tbody>
                    </table>
                    <p>Fees for preparing and submitting VAT returns are charged per return. Therefore if a business has annual turnover of £92,000 and doing quarterly returns it would be charged £120 per quarter or £480 per year for VAT work.</p>
                </div>

                <div class="fees-section">
                    <h3>Rental Property Income (Self-Assessment)</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Fee rate per return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Annual charge per property</td><td class="fee-highlight">£30 + VAT</td></tr>
                        </tbody>
                    </table>
                    <p>Fees for rental properties are charged annually per property included on the tax return. These fees are in addition to the standard £179 for a self assessment tax return.</p>
                    <p><strong>Example:</strong> If you share the ownership of a property you will only be charged once. Eg, husband and wife own 2 properties and both require self assessment tax returns. Fees are 2 x £30 plus 2 x £179 = £418 plus VAT total fees.</p>
                </div>

                <div class="fees-section">
                    <h3>PAYE</h3>
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Fee rate per return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Annual charge per employee</td><td class="fee-highlight">£120 + VAT</td></tr>
                        </tbody>
                    </table>
                    <p>Company directors often pay a monthly salary to make use of personal tax allowances. If a director wishes to pay over the lower earnings limit (£6396 for 2024-25) then PAYE registration and monthly submissions are needed. You may wish to do your own PAYE submissions or we can help.</p>
                    <p>We can also offer payroll for regular employees. Fees are based on a charge per year per employee. Because there is so much work involved in adding or removing employees the cost is the same if an employee is included for 1 month or 12 months. We find this system is simple to understand and administer.</p>
                    <p><strong>Auto Enrolment:</strong> If we are doing the payroll work we can also offer reporting for auto enrolment pensions (using NEST) at a cost of £30 per employee per year.</p>
                    <p><strong>Important:</strong> PAYE reporting is time sensitive, it is easy to get penalties if reporting is not completed on time. Many businesses do not need to register for PAYE so reporting may not be required. Our PAYE service is available if you are using us for year end accounting or VAT work.</p>
                    <p>There may be additional costs if you need help with PAYE or NEST registration and set up.</p>
                </div>

                <div class="fees-section">
                    <h3>Cloud Accounting Services</h3>
                    <p>We provide cloud accounting services based on your businesses specific needs. Because of the wide range of options here we struggle to provide a menu of services and costs.</p>
                    <p>You may wish us to review your own cloud bookkeeping work on a regular basis or you may wish us to do all of your bookkeeping for you. Reviews may occur on a monthly basis or perhaps quarterly to coincide with VAT quarters.</p>
                    <p>Our packages can include the subscription costs which may help to bring the total cost of the package down. Our software partners include Quickbooks and Xero.</p>
                    <p>There may be some upfront additional costs required to set up cloud accounting services. These tend to be on an hourly basis. We will aim to give a rough idea of time/cost and keep you updated if the level of work required changes at the earliest opportunity.</p>
                </div>

                <div class="note-box">
                    <h4>Important Information</h4>
                    <p><strong>Record Keeping:</strong> We appreciate that our fees are relatively low compared to other Chartered Accountants. In order to provide these low fee rates we do require that records are well maintained and the information provided to is accurate and delivered on a timely basis. If your records are in poor order we may look for additional fees to complete the necessary bookkeeping work to get them up to date.</p>
                    
                    <p><strong>Tax Planning:</strong> Sometimes the work we do can flow into complex tax planning matters which take up additional time than we make allowance for in our above fee schedules. If it looks like we are spending significant time on tax planning we will make you aware of this at the earliest opportunity and ideally at the initial meeting. We would look for extra fees based on the tax planning work required.</p>
                    
                    <p><strong>Other Work:</strong> Occasionally we are approached to do work which is outside of the normal fixed fee schedule items. A fixed fee for this type of work can normally be provided on request. If that is not possible we may be able to consider charging at an hourly rate.</p>
                    
                    <p><strong>Incomplete Work:</strong> We may be approached to take on work at a fixed fee and the work is started but not completed due to changes in the client circumstances. If this occurs we may look to invoice part of the agreed fixed fee based upon the level of completion of the work.</p>
                </div>

                <div class="note-box">
                    <h4>Invoicing and Payment Terms</h4>
                    <p>If we are preparing year end accounts or quarterly VAT returns we normally issue an invoice at the point where we issue draft numbers. We then go through a review process and identify any potential issues and this is followed by obtaining approval that the numbers are ready to be submitted.</p>
                    <p>At that stage all numbers are prepared to be submitted and we look for payment prior to actual submission. The final submission normally can take place on the same or following day and we can provide confirmation that submissions have been accepted.</p>
                </div>

                <div style="text-align: center; margin: 40px 0;">
                    <a href="../contact" class="btn btn-primary">
                        <i class="fas fa-envelope"></i>
                        Contact Us for a Quote
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 RJ Accountancy Limited. All rights reserved.</p>
            <p>Director: Rob Bonehill (FCA)</p>
        </div>
    </footer>
</body>
</html>
