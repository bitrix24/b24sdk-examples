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
            $contactEmail = $contactData->contact()->EMAIL[0]->VALUE;
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
<script src="https://api.bitrix24.com/api/v1/"></script>
<script>

    var documentId = 0;
    var contactEmail = '<?php echo $contactEmail; ?>';
    var contactId = <?php echo $contactId; ?>;
    var dealId = <?php echo $dealId; ?>;
    var documents = [];

    const layout = {
    blocks: {
        header: {
            type: "text",
            visible: true,
            properties: {
                value: "Подписать документ через Документолог",
                size: "xl",
            },
        },
        documentologInfoSection: {
            type: "section",
            visible: true,
            properties: {
                type: "withBorder",
                imageSrc: "https://i.ibb.co/VJy8LCm/timeline-icon-1.png",
                imageSize: "sm",
                blocks: {
                    documentologInfoPart1: {
                        type: "text",
                        properties: {
                            value: 'Подписывайте документы электронной подписью',
                            color: "base_70",
                            size: "md",
                            bold: true,
                        }
                    },
                    documentologInfoPart2: {
                        type: "link",
                        properties: {
                            text: 'Как работает подписание документов в Документолог?',
                            size: "sm",
                            action: {
                                type: "redirect",
                                uri: "https://documentolog.com/",
                            },
                        }
                    },
                }
            }
        },
        documentologDocumentSection: {
            type: "lineOfBlocks",
            visible: true,
            properties: {
                blocks: {
                    documentologDocumentBlock1: {
                        type: "text",
                        properties: {
                            value: "Документ на подпись: ",
                            color: "base_70"
                        }
                    },
                    documentologDocumentBlock2: {
                        type: "dropdownMenu",
                        properties: {
                            selectedValue: 0,
                            values: {
                                0: '- выберите документ -',
                                1: '1',
                                2: '2',
                            }
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
                            value: "Не выбран документ для отправки на подпись.",
                            color: "warning",
                        }
                    },
                    errorMessageNoContact: {
                        type: "text",
                        visible: false,
                        properties: {
                            value: "У сделки не указан контакт, который нужен, чтобы подписывать документы.",
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
                                        value: "Для отправки документа получателю у него должен быть указан e-mail. ",
                                        color: "warning"
                                    }
                                },
                                linkToContact: {
                                    type: "link",
                                    properties: {
                                        text: "Заполнить.",
                                        "action": {
                                            "type": "redirect",
                                            "uri": "/crm/contact/details/" + contactId + "/",
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
                            value: "Для отправки документа у вас должен быть указан e-mail в профиле Битрикс24.",
                            color: "warning",
                        }
                    },
                    errorMessageErrorWhileSending: {
                        type: "text",
                        visible: false,
                        properties: {
                            value: "Во время отправки документа произошла ошибка. Попробуйте позже.",
                            color: "warning",
                        }
                    },
                }
            }
        }
    },
    primaryButton: {
        title: "Отправить",
        state: false ? 'disabled' : 'normal'
    },
    secondaryButton: {
        title: "Отмена"
    }
};

function prepareLayout () {

    var result = {
        blocks: {
            header: {
                type: "text",
                visible: true,
                properties: {
                    value: "Подписать документ через Документолог",
                    size: "xl",
                },
            },
            documentologInfoSection: {
                type: "section",
                visible: true,
                properties: {
                    type: "withBorder",
                    imageSrc: "https://i.ibb.co/VJy8LCm/timeline-icon-1.png",
                    imageSize: "sm",
                    blocks: {
                        documentologInfoPart1: {
                            type: "text",
                            properties: {
                                value: 'Подписывайте документы электронной подписью',
                                color: "base_70",
                                size: "md",
                                bold: true,
                            }
                        },
                        documentologInfoPart2: {
                            type: "link",
                            properties: {
                                text: 'Как работает подписание документов в Документолог?',
                                size: "sm",
                                action: {
                                    type: "redirect",
                                    uri: "https://documentolog.com/",
                                },
                            }
                        },
                    }
                }
            },
            documentologDocumentSection: {
                type: "lineOfBlocks",
                visible: true,
                properties: {
                    blocks: {
                        documentologDocumentBlock1: {
                            type: "text",
                            properties: {
                                value: "Документ на подпись: ",
                                color: "base_70"
                            }
                        },
                        documentologDocumentBlock2: {
                            type: "dropdownMenu",
                            properties: {
                                selectedValue: 0,
                                values: documents.reduce((acc, document) => {
                                    acc[document.id] = document.title;
                                    return acc;
                                }, {0: '- выберите документ -'})
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
                                value: "Не выбран документ для отправки на подпись.",
                                color: "warning",
                            }
                        },
                        errorMessageNoContact: {
                            type: "text",
                            visible: false,
                            properties: {
                                value: "У сделки не указан контакт, который нужен, чтобы подписывать документы.",
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
                                            value: "Для отправки документа получателю у него должен быть указан e-mail. ",
                                            color: "warning"
                                        }
                                    },
                                    linkToContact: {
                                        type: "link",
                                        properties: {
                                            text: "Заполнить.",
                                            "action": {
                                                "type": "redirect",
                                                "uri": "/crm/contact/details/" + contactId + "/",
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
                                value: "Для отправки документа у вас должен быть указан e-mail в профиле Битрикс24.",
                                color: "warning",
                            }
                        },
                        errorMessageErrorWhileSending: {
                            type: "text",
                            visible: false,
                            properties: {
                                value: "Во время отправки документа произошла ошибка. Попробуйте позже.",
                                color: "warning",
                            }
                        },
                    }
                }
            }
        },
        primaryButton: {
            title: "Отправить",
            state: false ? 'disabled' : 'normal'
        },
        secondaryButton: {
            title: "Отмена"
        }
    };

    console.log('prepared layout: ', result);

    return result;
}

function prepareError (errorBlockId, show = true) {
    console.log('errorBlockId: ', errorBlockId);
    console.log('errorBlockId: ', show);
    BX24.placement.call('setLayoutItemState', {id: errorBlockId, visible: show});
}

function displayError (show = true) {
    BX24.placement.call('setLayoutItemState', {id: "errorMessageSection", visible: show});
}

function updateDealData () {
    BX24.callMethod('crm.deal.get', {id: dealId}, function(result) {
        console.log('Deal data: ', result.data());

        contactId = result.data().CONTACT_ID;

        BX24.callMethod('crm.contact.get', {id: contactId}, function(contactResult) {
            console.log('Contact data: ', contactResult.data());

            contactEmail = contactResult.data().EMAIL[0].VALUE;

            checkErrors();
        });
    });
}

function getDocuments () {
    BX24.callMethod(
        'crm.documentgenerator.document.list', 
        {
            'filter': {
                "entityTypeId": 2, // сделки
                "entityId": dealId
            }
        }, 
        function(result) {
            var documentData = result.data();

            console.log('Documents: ', result.data());
            if (documentData.documents.length > 0) {
                documents = documentData.documents.map(doc => ({
                    id: doc.id,
                    title: doc.title
                }));
                console.log('prepared documents: ', documents);

                BX24.placement.call('setLayout', prepareLayout());
            }
            
        }
    );
}

function checkErrors() {
    prepareError('errorMessageNoDocument', (documentId === 0));
    prepareError('errorMessageNoContact', (contactId === 0));
    
    if (contactId !== 0) prepareError('errorMessageNoClientEmail', (contactEmail === ''));
    // prepareError('errorMessageNoEmployeeEmail', false);
    prepareError('errorMessageErrorWhileSending', false);

    console.log('check contactEmail: ', (contactEmail === ''));
    console.log('check contactId: ', (contactId === 0));
    console.log('check documentId: ', (documentId === 0));

    return (documentId === 0 || contactEmail === '');
}

document.addEventListener("DOMContentLoaded", function() {
    BX24.init(function() {
        console.log("BX24 initialized successfully.");

        console.log('contactEmail: ', contactEmail);
        console.log('documentId: ', documentId);
        console.log('contactId: ', contactId);

        getDocuments();

        // клик по кнопке "Отправить":
        BX24.placement.call('bindPrimaryButtonClickCallback', null, () => {
            BX24.placement.call('lock'); // заблокировать интерфейс

            if (checkErrors() !== false) {

                displayError(true);

                BX24.placement.call('unlock');
                return;
            }

            displayError(false);

            // Выполняем какие-то действия. Запрос в бэк, например
            const authInfo = BX24.getAuth();

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

                        BX24.placement.call('unlock'); // разблокировать интерфейс
                        BX24.placement.call('finish'); // подать встройке сигнал, что форма успешно обработана (ошибок нет)

                        return response.json();
                    } else {
                        return response.text().then(text => {
                            throw new Error('Unexpected response format: ' + text);
                        });
                    }
                })
                .then(data => {
                    console.log('Sending result:', data);
                    if (data.error) {

                        prepareError('errorMessageErrorWhileSending', true);
                        displayError(true);

                        BX24.placement.call('unlock');
                        return;
                    }
                })
                .catch(e => {
                    console.error('Error:', e.message);
                    
                    prepareError('errorMessageErrorWhileSending', true);
                    displayError(true);

                    BX24.placement.call('unlock');

                    return;
                });
            } catch (sendingError) {
                
                console.error('Unexpected error:', sendingError);
                
                prepareError('errorMessageErrorWhileSending', true);
                displayError(true);

                BX24.placement.call('unlock');

                return;
            }
        });

        // клик по кнопке "Отмена":
        BX24.placement.call('bindSecondaryButtonClickCallback', null, () => {
            BX24.placement.call('finish');
        });

        BX24.placement.call('bindValueChangeCallback', 'documentologDocumentBlock2', (data) => {
            documentId = parseInt(data.value, 10);
            // displayError('errorMessageNoDocument');
            
            console.log('Выбран документ: ', data);
            console.log('documentId: ', documentId);
        });

        BX24.placement.call('bindEntityUpdateCallback', null, () => {
            console.log('Entity updated');
            updateDealData();
        });

    });
});

</script>