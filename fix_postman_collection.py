import json
import re

# Read the original collection
with open('WANIGO_API (Full Access).postman_collection.json', 'r', encoding='utf-8') as f:
    collection = json.load(f)

def fix_auth_in_request(request):
    """Fix authentication in a single request"""
    if not isinstance(request, dict):
        return request
    
    # Fix bearer auth block
    if 'auth' in request and request['auth'].get('type') == 'bearer':
        bearer_list = request['auth'].get('bearer', [])
        for item in bearer_list:
            if item.get('key') == 'token':
                # Check if it's a hardcoded token (pattern: number|alphanumeric)
                value = item.get('value', '')
                if re.match(r'^\d+\|[a-zA-Z0-9]+', value):
                    print(f"  ✓ Fixed hardcoded token in bearer auth: {value[:20]}...")
                    item['value'] = '{{token}}'
                elif value != '{{token}}' and value != '':
                    print(f"  ✓ Standardized token variable: {value}")
                    item['value'] = '{{token}}'
    
    # Fix Authorization header
    if 'header' in request:
        for header in request['header']:
            if header.get('key') == 'Authorization':
                value = header.get('value', '')
                # Check if it's not using variable
                if value and not value.startswith('Bearer {{'):
                    print(f"  ✓ Fixed Authorization header: {value[:30]}...")
                    header['value'] = 'Bearer {{token}}'
    
    return request

def process_items(items, level=0):
    """Recursively process collection items"""
    if not isinstance(items, list):
        return
    
    for item in items:
        if not isinstance(item, dict):
            continue
            
        # Print item name
        indent = "  " * level
        item_name = item.get('name', 'Unknown')
        
        # If item has a request, fix it
        if 'request' in item:
            print(f"{indent}📝 Processing: {item_name}")
            item['request'] = fix_auth_in_request(item['request'])
        
        # If item has sub-items, process them recursively
        if 'item' in item:
            process_items(item['item'], level + 1)

# Process the collection
print("🔧 Fixing WANIGO API Postman Collection...")
print("=" * 60)

if 'item' in collection:
    process_items(collection['item'])

# Save the fixed collection
output_file = 'WANIGO_API_Fixed.postman_collection.json'
with open(output_file, 'w', encoding='utf-8') as f:
    json.dump(collection, f, indent='\t', ensure_ascii=False)

print("=" * 60)
print(f"✅ Fixed collection saved to: {output_file}")
print("\n📋 Summary:")
print("  - Replaced all hardcoded bearer tokens with {{token}}")
print("  - Standardized Authorization headers to use Bearer {{token}}")
print("  - Collection is now ready to use with environment variables")
print("\n🚀 Next steps:")
print("  1. Import WANIGO_API_Fixed.postman_collection.json to Postman")
print("  2. Import WANIGO_API.postman_environment.json to Postman")
print("  3. Select 'WANIGO API - Local' environment")
print("  4. Run Login request to auto-save token")
print("  5. All authenticated requests will now work!")
