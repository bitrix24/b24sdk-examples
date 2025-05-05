<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpFoundation\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

$errorInfo = null;
$result = null;
$contactEmail = '';

$incomingRequest = Request::createFromGlobals();
Application::getLog()->debug('handler.start', [
	'request' => $incomingRequest->request->all(),
	'query' => $incomingRequest->query->all()
]);

try
{
	// Retrieve the Bitrix24 service.
	$B24 = Application::getB24Service($incomingRequest);
	
	$placementOptions = json_decode($incomingRequest->request->get('PLACEMENT_OPTIONS'), true);
    $dealId = intval($placementOptions['entityId']);

	$dealData = $B24->getCRMScope()->deal()->get($dealId);
    if ($dealData->deal()->ID > 0)
    {
        $contactId = intval($dealData->deal()->CONTACT_ID);

        if ($contactId !== 0)
        {
            $contactData = $B24->getCRMScope()->contact()->get($contactId);
            $contactEmail = count($contactData->contact()->EMAIL) > 0 ? $contactData->contact()->EMAIL[0]->VALUE : '';
        }
    }
    else
    {
        $errorInfo = sprintf("Error: %s", "Deal not found.");
    }
}catch(\Exception $exception)
{
	$errorInfo = sprintf("Exception: %s <br><small>Line: %s<br>File: %s<small>", $exception->getMessage(), $exception->getLine(), $exception->getFile());
}

?>
<script src="https://unpkg.com/@bitrix24/b24jssdk@latest/dist/umd/index.min.js"></script>
<script type="module">

    var documentId = 0;
    var contactEmail = '<?php echo $contactEmail; ?>';
    var contactId = <?php echo $contactId; ?>;
    var dealId = <?php echo $dealId; ?>;
    var documents = [];

    const $logger = B24Js.LoggerBrowser.build(
		'e-sign-demo: handler',
		true
	);
	
	const $b24 = await B24Js.initializeB24Frame();
	$b24.setLogger(
		B24Js.LoggerBrowser.build('Core')
	);
	
	$logger.warn('B24Frame.init');

    $logger.info('contactEmail >> ',contactEmail);
    $logger.info('documentId >> ',documentId);
    $logger.info('contactId >> ',contactId);
    $logger.info('dealId >> ',dealId);
    $logger.info('documents >> ',documents);

    getDocuments();

    // Click on the "Send" button
    $b24.placement.callCustomBind('bindPrimaryButtonClickCallback', null, () => {

        $logger.info('bindPrimaryButtonClickCallback started');

        $b24.placement.call('lock'); // lock user's interface

        if (checkErrors() !== false) {

            displayError(true);

            $b24.placement.call('unlock');
            return;
        }

        displayError(false);

        // Perform some actions, such as a request to the backend

        const authInfo = $b24.auth.getAuthData();

        try {
            fetch('action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'documentId': documentId,
                    'authJson': JSON.stringify(authInfo),
                    'action': 'signDocument',
                    'dealId': dealId,
                    'contactEmail': contactEmail,
                    'employeeEmail': 'no@mail.com'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.indexOf('application/json') !== -1) {

                    $b24.placement.call('unlock'); // unlock user's interface
                    $b24.placement.call('finish'); // signal to the framework that the form has been processed successfully (no errors)

                    return response.json();
                } else {
                    return response.text().then(text => {
                        throw new Error('Unexpected response format: ' + text);
                    });
                }
            })
            .then(data => {
                
                $logger.info('Sending result:', data);
                if (data.error) {

                    prepareError('errorMessageErrorWhileSending', true);
                    displayError(true);

                    $b24.placement.call('unlock');
                    return;
                }
            })
            .catch(e => {
                console.error('Error:', e.message);
                
                prepareError('errorMessageErrorWhileSending', true);
                displayError(true);

                $b24.placement.call('unlock');

                return;
            });
        } catch (sendingError) {
            
            // console.error('Unexpected error:', sendingError);
            $logger.error('Unexpected error:', sendingError);
            
            prepareError('errorMessageErrorWhileSending', true);
            displayError(true);

            $b24.placement.call('unlock');

            return;
        }
    });

    // click on the "Cancel" button:
    $b24.placement.callCustomBind('bindSecondaryButtonClickCallback', null, () => {
        $b24.placement.call('finish');
    });

    $b24.placement.callCustomBind('bindValueChangeCallback', 'esignServiceDocumentBlock2', (data) => {
        documentId = parseInt(data.value, 10);

        $logger.info('documentId: ', documentId);
    });

    $b24.placement.callCustomBind('bindEntityUpdateCallback', null, () => {
        $logger.info('Entity updated');
        updateDealData();
    });


function prepareLayout () {

    var result = {
        blocks: {
            header: {
                type: "text",
                visible: true,
                properties: {
                    value: "Sign the document via E-Sign Service",
                    size: "xl",
                },
            },
            esignServiceInfoSection: {
                type: "section",
                visible: true,
                properties: {
                    type: "withBorder",
                    imageSrc: "https://i.ibb.co/VJy8LCm/timeline-icon-1.png",
                    imageSize: "sm",
                    blocks: {
                        esignServiceInfoPart1: {
                            type: "text",
                            properties: {
                                value: 'Sign documents with an electronic signature',
                                color: "base_70",
                                size: "md",
                                bold: true,
                            }
                        },
                        esignServiceInfoPart2: {
                            type: "link",
                            properties: {
                                text: 'How does signing documents work in E-Sign Service?',
                                size: "sm",
                                action: {
                                    type: "redirect",
                                    value: "https://esign-system.com/help/",
                                },
                            }
                        },
                    }
                }
            },
            esignServiceDocumentSection: {
                type: "lineOfBlocks",
                visible: true,
                properties: {
                    blocks: {
                        esignServiceDocumentBlock1: {
                            type: "text",
                            properties: {
                                value: "Document for signature: ",
                                color: "base_70"
                            }
                        },
                        esignServiceDocumentBlock2: {
                            type: "dropdownMenu",
                            properties: {
                                selectedValue: 0,
                                values: documents.reduce((acc, document) => {
                                    acc[document.id] = document.title;
                                    return acc;
                                }, {0: '- select a document -'})
                            }
                        },
                    }
                }
            },
            errorMessageSection: {
                type: "section",
                visible: false,
                properties: {
                    type: "warning",
                    blocks: {
                        errorMessageNoDocument: {
                            type: "text",
                            visible: false,
                            properties: {
                                value: "No document selected for signing.",
                                color: "warning",
                            }
                        },
                        errorMessageNoContact: {
                            type: "text",
                            visible: false,
                            properties: {
                                value: "The deal does not have a contact specified, which is required to sign documents.",
                                color: "warning",
                            }
                        },
                        errorMessageNoClientEmail: {
                            type: "lineOfBlocks",
                            visible: false,
                            properties: {
                                blocks: {
                                    errorMsg: {
                                        type: "text",
                                        properties: {
                                            value: "To send the document to the recipient, they must have an email address specified. ",
                                            color: "warning"
                                        }
                                    },
                                    linkToContact: {
                                        type: "link",
                                        properties: {
                                            text: "Fill in.",
                                            action: {
                                                type: "redirect",
                                                value: "/crm/contact/details/" + contactId + "/",
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        errorMessageNoEmployeeEmail: {
                            type: "text",
                            visible: false,
                            properties: {
                                value: "To send the document, you must have an email address specified in your Bitrix24 profile.",
                                color: "warning",
                            }
                        },
                        errorMessageErrorWhileSending: {
                            type: "text",
                            visible: false,
                            properties: {
                                value: "An error occurred while sending the document. Please try again later.",
                                color: "warning",
                            }
                        },
                    }
                }
            }
        },
        primaryButton: {
            title: "Send",
            state: false ? 'disabled' : 'normal'
        },
        secondaryButton: {
            title: "Cancel"
        }
    };

    $logger.info('prepared layout >> ', result);
    // console.log('prepared layout: ', result);

    return result;
}

function prepareError (errorBlockId, show = true) {
    $logger.info('errorBlockId >> ', errorBlockId);

    $logger.info('errorBlockId >> ', show);

    $b24.placement.call('setLayoutItemState', {id: errorBlockId, visible: show});
}

function displayError (show = true) {
    $b24.placement.call('setLayoutItemState', {id: "errorMessageSection", visible: show});
}

async function updateDealData () {

    let response = await $b24.callMethod('crm.deal.get', {id: dealId});
	const dealData = response.getData().result;

    $logger.info('dealData >> ', dealData);

    contactId = dealData.CONTACT_ID;
    $logger.info('contactId >> ', contactId);

    if (contactId > 0) {
        response = await $b24.callMethod('crm.contact.get', {id: contactId});
        const contactData = response.getData().result;
        $logger.info('contactData >> ', contactData);

        contactEmail = (contactData.EMAIL && contactData.EMAIL.length > 0) ? contactData.EMAIL[0].VALUE : '';
        $logger.info('contactEmail >> ', contactEmail);
    } else {
        contactEmail = '';
    }

    checkErrors();
}

async function getDocuments () {

    let response = await $b24.callMethod('crm.documentgenerator.document.list', {
        'filter': {
            "entityTypeId": 2, // deals
            "entityId": dealId
        }
    });

	const docData = response.getData().result;

    $logger.info('docData >> ', docData);

    if (docData.documents.length > 0) {
        documents = docData.documents.map(doc => ({
            id: doc.id,
            title: doc.title
        }));
        $logger.info('documents >> ', documents);

        $b24.placement.call('setLayout', prepareLayout())
    }
};

function checkErrors() {
    prepareError('errorMessageNoDocument', (documentId === 0));
    prepareError('errorMessageNoContact', (contactId === 0));
    
    if (contactId !== 0) prepareError('errorMessageNoClientEmail', (contactEmail === ''));

    prepareError('errorMessageErrorWhileSending', false);

    $logger.info('check contactEmail >> ', (contactEmail === ''));
    $logger.info('check contactId >> ', (contactId === 0));
    $logger.info('check documentId >> ', (documentId === 0));

    return (documentId === 0 || contactEmail === '');
}

</script>