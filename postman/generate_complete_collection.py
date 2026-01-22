#!/usr/bin/env python3
"""
Generate complete Postman collection for WANIGO API
Combines all endpoints into one comprehensive collection
"""

import json

def create_complete_collection():
    collection = {
        "info": {
            "_postman_id": "wanigo-complete-2026-01-22",
            "name": "WANIGO API - Complete Collection (2026)",
            "description": "Complete API collection for WANIGO Waste Management System including all endpoints",
            "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
        },
        "item": [],
        "variable": [
            {"key": "base_url", "value": "http://localhost:8000", "type": "string"},
            {"key": "token", "value": "", "type": "string"}
        ]
    }
    
    # 1. Authentication
    auth_folder = {
        "name": "1. Authentication",
        "item": [
            create_request("Check Email", "POST", "/api/check-email", 
                body={"email": "{{test_email}}"}),
            create_request("Register", "POST", "/api/register",
                body={"name": "Test User", "email": "{{test_email}}", "password": "{{test_password}}", 
                      "password_confirmation": "{{test_password}}", "phone_number": "{{test_phone}}", "role": "nasabah"}),
            create_request("Login", "POST", "/api/login",
                body={"email": "{{test_email}}", "password": "{{test_password}}"},
                test_script='var jsonData = pm.response.json(); if (jsonData.data && jsonData.data.access_token) { pm.environment.set("token", jsonData.data.access_token); }'),
            create_request("Logout", "POST", "/api/logout", auth=True),
            create_request("Forgot Password", "POST", "/api/forgot-password",
                body={"email": "{{test_email}}"}),
            create_request("Reset Password", "POST", "/api/reset-password",
                body={"token": "reset-token", "email": "{{test_email}}", "password": "NewPass123!", "password_confirmation": "NewPass123!"}),
            create_request("Get Profile", "GET", "/api/profile", auth=True),
            create_request("Update Profile", "POST", "/api/update-profile", auth=True,
                body={"name": "Updated Name", "phone_number": "08123456789"}),
            create_request("Update Password", "POST", "/api/update-password", auth=True,
                body={"current_password": "{{test_password}}", "password": "NewPass123!", "password_confirmation": "NewPass123!"}),
            create_request("Check Profile Status", "GET", "/api/profile-status", auth=True)
        ]
    }
    
    # 2. Bank Sampah (Public)
    bank_public_folder = {
        "name": "2. Bank Sampah (Public)",
        "item": [
            create_request("Get All Bank Sampah", "GET", "/api/bank-sampah",
                query=[
                    {"key": "q", "value": "mojo", "disabled": True},
                    {"key": "lat", "value": "-7.2575"},
                    {"key": "lng", "value": "112.7521"},
                    {"key": "radius_km", "value": "10"},
                    {"key": "kategori", "value": "kering"},
                    {"key": "sort", "value": "distance"},
                    {"key": "per_page", "value": "20"},
                    {"key": "page", "value": "1"}
                ]),
            create_request("Get Bank Sampah Detail", "GET", "/api/bank-sampah/:id",
                variables=[{"key": "id", "value": "1"}])
        ]
    }
    
    # 3. Bank Sampah (Authenticated)
    bank_auth_folder = {
        "name": "3. Bank Sampah (Authenticated)",
        "item": [
            create_request("Get Top Frequency", "GET", "/api/nasabah/bank-sampah/top-frequency", auth=True),
            create_request("Get Bank Sampah List (Old)", "GET", "/api/nasabah/bank-sampah", auth=True,
                query=[{"key": "keyword", "value": "mojo"}, {"key": "status_operasional", "value": "1"}]),
            create_request("Find Nearby", "POST", "/api/nasabah/bank-sampah/find-nearby", auth=True,
                body={"latitude": -7.2575, "longitude": 112.7521, "radius": 10, "kategori_sampah": 0}),
            create_request("Get User's Bank Sampah", "GET", "/api/nasabah/bank-sampah/list", auth=True),
            create_request("Map Filter", "POST", "/api/nasabah/bank-sampah/map-filter", auth=True,
                body={"latitude": -7.2575, "longitude": 112.7521, "radius": 10, "kategori_sampah": 0})
        ]
    }
    
    # 4. Katalog Sampah (NEW)
    katalog_folder = {
        "name": "4. Katalog Sampah",
        "item": [
            create_request("Get Katalog by Bank (NEW)", "GET", "/api/bank-sampah/:bank_sampah_id/katalog",
                variables=[{"key": "bank_sampah_id", "value": "1"}],
                query=[
                    {"key": "kategori", "value": "kering"},
                    {"key": "sub_kategori_id", "value": "1", "disabled": True},
                    {"key": "per_page", "value": "20"},
                    {"key": "page", "value": "1"}
                ],
                description="NEW: Get katalog with sub-kategori info, filtering, and pagination"),
            create_request("Get Katalog by Bank (Old)", "GET", "/api/nasabah/katalog-sampah/by-bank", auth=True,
                query=[{"key": "bank_sampah_id", "value": "1"}, {"key": "kode_kategori", "value": "kering"}]),
            create_request("Get Katalog Detail", "GET", "/api/nasabah/katalog-sampah/:id", auth=True,
                variables=[{"key": "id", "value": "1"}]),
            create_request("Search Katalog", "GET", "/api/nasabah/katalog-sampah/search", auth=True,
                query=[{"key": "bank_sampah_id", "value": "1"}, {"key": "keyword", "value": "plastik"}]),
            create_request("Get Katalog for Setoran", "GET", "/api/nasabah/katalog-sampah/for-setoran", auth=True,
                query=[{"key": "bank_sampah_id", "value": "1"}, {"key": "kode_kategori", "value": "kering"}])
        ]
    }
    
    # 5. Sub-Kategori Sampah (NEW)
    subkategori_folder = {
        "name": "5. Sub-Kategori Sampah (NEW)",
        "item": [
            create_request("Get Sub-Kategori by Bank (NEW)", "GET", "/api/bank-sampah/:bank_sampah_id/sub-kategori",
                variables=[{"key": "bank_sampah_id", "value": "1"}],
                query=[{"key": "kategori", "value": "semua"}],
                description="NEW: Get sub-categories grouped by kering/basah with icon, warna, slug"),
            create_request("Get Sub-Kategori List (Old)", "GET", "/api/nasabah/sub-kategori-sampah", auth=True,
                query=[{"key": "bank_sampah_id", "value": "1"}, {"key": "kode_kategori", "value": "kering"}]),
            create_request("Get Katalog by Sub-Kategori", "GET", "/api/nasabah/sub-kategori-sampah/katalog", auth=True,
                query=[{"key": "sub_kategori_id", "value": "1"}])
        ]
    }
    
    # 6. Dashboard
    dashboard_folder = {
        "name": "6. Dashboard",
        "item": [
            create_request("Get Dashboard Statistics", "GET", "/api/nasabah/dashboard/stats", auth=True,
                description="Get total_saldo, total_tonase, total_setoran, total_bank_sampah")
        ]
    }
    
    # 7. Penarikan Saldo
    withdrawal_folder = {
        "name": "7. Penarikan Saldo (Withdrawal)",
        "item": [
            create_request("Get Withdrawal History", "GET", "/api/nasabah/penarikan-saldo", auth=True,
                query=[{"key": "page", "value": "1"}, {"key": "per_page", "value": "20"}]),
            create_request("Create Withdrawal", "POST", "/api/nasabah/penarikan-saldo", auth=True,
                body_type="formdata",
                formdata=[
                    {"key": "bank_sampah_id", "value": "1", "type": "text"},
                    {"key": "jumlah_penarikan", "value": "50000", "type": "text"},
                    {"key": "foto_buku_tabungan", "type": "file", "src": []}
                ]),
            create_request("Get Withdrawal Detail", "GET", "/api/nasabah/penarikan-saldo/:id", auth=True,
                variables=[{"key": "id", "value": "1"}]),
            create_request("Approve Withdrawal (Petugas)", "POST", "/api/nasabah/penarikan-saldo/:id/approve", auth=True,
                variables=[{"key": "id", "value": "1"}],
                body={}),
            create_request("Complete Withdrawal (Nasabah)", "POST", "/api/nasabah/penarikan-saldo/:id/complete", auth=True,
                variables=[{"key": "id", "value": "1"}],
                body={"kode_verifikasi": "ABC123"})
        ]
    }
    
    # 8. Nasabah Profile
    profile_folder = {
        "name": "8. Nasabah Profile",
        "item": [
            create_request("Get Nasabah Profile", "GET", "/api/nasabah/profile", auth=True),
            create_request("Update Profile Step 1", "POST", "/api/nasabah/profile/step1", auth=True,
                body={"jenis_kelamin": "Laki-laki", "usia": "18 hingga 34 tahun", "profesi": "Programmer"}),
            create_request("Update Profile Step 2", "POST", "/api/nasabah/profile/step2", auth=True,
                body={"tahu_memilah_sampah": "Sudah tahu", "motivasi_memilah_sampah": "Menjaga lingkungan", 
                      "nasabah_bank_sampah": "Tidak, belum", "kode_bank_sampah": ""}),
            create_request("Update Profile Step 3", "POST", "/api/nasabah/profile/step3", auth=True,
                body={"frekuensi_memilah_sampah": "Setiap minggu", "jenis_sampah_dikelola": "Plastik"})
        ]
    }
    
    # 9. Education (Edukasi)
    education_folder = {
        "name": "9. Education (Edukasi)",
        "item": [
            create_request("Get Modul List", "GET", "/api/nasabah/edukasi/moduls", auth=True),
            create_request("Get Modul Detail", "GET", "/api/nasabah/edukasi/modul/:id", auth=True,
                variables=[{"key": "id", "value": "1"}]),
            create_request("Get Video Detail", "GET", "/api/nasabah/edukasi/video/:id", auth=True,
                variables=[{"key": "id", "value": "1"}]),
            create_request("Get Article Detail", "GET", "/api/nasabah/edukasi/artikel/:id", auth=True,
                variables=[{"key": "id", "value": "1"}]),
            create_request("Mark Content Complete", "POST", "/api/nasabah/edukasi/konten/:id/complete", auth=True,
                variables=[{"key": "id", "value": "1"}])
        ]
    }
    
    # Add all folders to collection
    collection["item"] = [
        auth_folder,
        bank_public_folder,
        bank_auth_folder,
        katalog_folder,
        subkategori_folder,
        dashboard_folder,
        withdrawal_folder,
        profile_folder,
        education_folder
    ]
    
    return collection

def create_request(name, method, url, auth=False, body=None, body_type="raw", formdata=None, 
                   query=None, variables=None, description="", test_script=""):
    """Helper function to create a Postman request"""
    request = {
        "name": name,
        "request": {
            "method": method,
            "header": [
                {"key": "Accept", "value": "application/json", "type": "text"},
                {"key": "Content-Type", "value": "application/json", "type": "text"}
            ],
            "url": {
                "raw": "{{base_url}}" + url,
                "host": ["{{base_url}}"],
                "path": url.strip("/").split("/")
            }
        }
    }
    
    if description:
        request["request"]["description"] = description
    
    if auth:
        request["request"]["header"].append({
            "key": "Authorization",
            "value": "Bearer {{token}}",
            "type": "text"
        })
    
    if body is not None:
        if body_type == "formdata":
            request["request"]["body"] = {
                "mode": "formdata",
                "formdata": formdata or []
            }
        else:
            request["request"]["body"] = {
                "mode": "raw",
                "raw": json.dumps(body, indent=4)
            }
    
    if query:
        request["request"]["url"]["query"] = query
    
    if variables:
        request["request"]["url"]["variable"] = variables
    
    if test_script:
        request["event"] = [{
            "listen": "test",
            "script": {
                "exec": [test_script],
                "type": "text/javascript"
            }
        }]
    
    return request

def create_environment():
    """Create Postman environment file"""
    environment = {
        "id": "wanigo-api-env-2026",
        "name": "WANIGO API - Complete Environment",
        "values": [
            {"key": "base_url", "value": "http://localhost:8000", "type": "default", "enabled": True},
            {"key": "token", "value": "", "type": "secret", "enabled": True},
            {"key": "test_email", "value": "test@example.com", "type": "default", "enabled": True},
            {"key": "test_password", "value": "Password123!", "type": "secret", "enabled": True},
            {"key": "test_phone", "value": "08123456789", "type": "default", "enabled": True},
            {"key": "bank_sampah_id", "value": "1", "type": "default", "enabled": True},
            {"key": "katalog_id", "value": "1", "type": "default", "enabled": True},
            {"key": "sub_kategori_id", "value": "1", "type": "default", "enabled": True}
        ],
        "_postman_variable_scope": "environment",
        "_postman_exported_at": "2026-01-22T10:00:00.000Z",
        "_postman_exported_using": "Postman/11.0.0"
    }
    return environment

if __name__ == "__main__":
    # Generate collection
    collection = create_complete_collection()
    with open("WANIGO_API_Complete.postman_collection.json", "w", encoding="utf-8") as f:
        json.dump(collection, f, indent=2, ensure_ascii=False)
    print("✅ Generated: WANIGO_API_Complete.postman_collection.json")
    
    # Generate environment
    environment = create_environment()
    with open("WANIGO_API_Complete.postman_environment.json", "w", encoding="utf-8") as f:
        json.dump(environment, f, indent=2, ensure_ascii=False)
    print("✅ Generated: WANIGO_API_Complete.postman_environment.json")
    
    print("\n📦 Files generated successfully!")
    print("📝 Import both files into Postman to get started")
    print("🔑 After login, the token will be automatically saved to environment")
