import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config.dart';

class GeocodeResult {
  final int? communeId;
  final String? communeNom;
  final String? quartier;

  GeocodeResult({this.communeId, this.communeNom, this.quartier});
}

class ApiService {
  static Map<String, String> get _headers => {'X-Api-Key': mobileApiKey};

  static Future<List<Map<String, dynamic>>> fetchCommunes() async {
    final uri = Uri.parse('$apiRootUrl/contact/api_communes.php');
    final response = await http.get(uri, headers: _headers);
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return List<Map<String, dynamic>>.from(body['data']);
    }
    throw Exception(body['message'] ?? 'Impossible de charger les communes.');
  }

  static Future<GeocodeResult> reverseGeocode(double lat, double lon) async {
    final uri = Uri.parse('$apiRootUrl/contact/api_geocode.php?lat=$lat&lon=$lon');
    final response = await http.get(uri);
    final body = jsonDecode(response.body);
    if (body['success'] != true) return GeocodeResult();
    final commune = body['commune'];
    return GeocodeResult(
      communeId: commune != null ? commune['id'] as int : null,
      communeNom: commune != null ? commune['nom'] as String : null,
      quartier: body['quartier'] as String?,
    );
  }

  static Future<List<Map<String, dynamic>>> fetchAlertesRecentes() async {
    final uri = Uri.parse('$apiRootUrl/historiques/api_historiques.php');
    final response = await http.get(uri);
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return List<Map<String, dynamic>>.from(body['data']);
    }
    throw Exception(body['message'] ?? 'Impossible de charger les alertes récentes.');
  }

  static String fileUrl(String fichier) => '$apiRootUrl/contact/informations/$fichier';

  static Future<String> submitAlerte({
    required int communeId,
    required String quartier,
    required String risque,
    required String description,
    DateTime? date,
    required double latitude,
    required double longitude,
    required File file,
  }) async {
    final uri = Uri.parse('$apiRootUrl/contact/api_alerte.php');
    final request = http.MultipartRequest('POST', uri)
      ..headers.addAll(_headers)
      ..fields['commune'] = communeId.toString()
      ..fields['quartier'] = quartier
      ..fields['risque'] = risque
      ..fields['description'] = description
      ..fields['latitude'] = latitude.toString()
      ..fields['longitude'] = longitude.toString();

    if (date != null) {
      request.fields['date'] =
          '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
    }

    request.files.add(await http.MultipartFile.fromPath('file', file.path));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    final body = jsonDecode(response.body);

    if (response.statusCode == 200 && body['success'] == true) {
      return body['message'] ?? 'Alerte enregistrée avec succès.';
    }
    throw Exception(body['message'] ?? "Erreur lors de l'envoi de l'alerte.");
  }
}
