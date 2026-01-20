import json

# Baca file Postman collection
with open('WANIGO_API_Fixed.postman_collection.json', 'r', encoding='utf-8') as f:
    collection = json.load(f)

# Data folder Laporan Sampah (TF 4)
laporan_sampah_folder = {
    "name": "Laporan Sampah (TF 4)",
    "item": [
        {
            "name": "Get Bank Sampah List (dengan Saldo)",
            "request": {
                "auth": {
                    "type": "bearer",
                    "bearer": [
                        {
                            "key": "token",
                            "value": "{{token}}",
                            "type": "string"
                        }
                    ]
                },
                "method": "GET",
                "header": [
                    {
                        "key": "Accept",
                        "value": "application/json",
                        "type": "text"
                    }
                ],
                "url": {
                    "raw": "{{base_url}}/api/nasabah/laporan-sampah/bank-sampah-list",
                    "host": ["{{base_url}}"],
                    "path": ["api", "nasabah", "laporan-sampah", "bank-sampah-list"]
                },
                "description": "Mendapatkan daftar bank sampah yang terdaftar oleh nasabah beserta saldo tabungan"
            },
            "response": []
        }
    ]
}

print("Script siap dijalankan...")
